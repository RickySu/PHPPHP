#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include "h/ZVAL.h"
#include "h/ZVAL_LIST.h"
#include "h/dtoa.h"

void __attribute((fastcall)) freeConvertionCacheBuffer(zval *zval) {
    if (zval->_convertion_cache_type == ZVAL_TYPE_STRING) {
        efree(zval->_convertion_cache.str.val);
        zval->_convertion_cache.str.len = 0;
    }
    zval->_convertion_cache_type = ZVAL_TYPE_NULL;
}

zval * __attribute((fastcall)) ZVAL_INIT(zvallist *list) {
    zval * aZval;
    if (list->isTemp) {
        ZVAL_TEMP_LIST_GC_MIN(list);
    }

    aZval = ecalloc(1, sizeof (zval));

    if (list) {
        ZVAL_GC_REGISTER(list, aZval);
    }
    aZval->refcount = 1;
    return aZval;
}

void __attribute((fastcall)) ZVAL_GC_REGISTER(zvallist *list, zval *zval) {
    if (!list->isTemp) {
        if (list->count == list->len) {
            list->next = ZVAL_LIST_INIT();
            ZVAL_GC_REGISTER(list->next, zval);
            return;
        }
    }
    list->zval[list->count++] = zval;
}

void __attribute((fastcall)) ZVAL_TEMP_GC(zvallist *list, zval *varZval) {
    int i, j;
    for (i = 0; i < list->count; i++) {
        if (list->zval[i] == varZval) {
            for (j = i; j < list->count - 1; j++) {
                list->zval[j] = list->zval[j + 1];
            }
            list->count--;
            break;
        }
    }
}

void __attribute((fastcall)) ZVAL_GC(zvallist *list, zval *varZval) {
    int i;
    if (--varZval->refcount) {
        if (varZval->refcount == 1) {
            varZval->is_ref = 0;
        }
        return;
    }
    if (list) {
        if (list->isTemp) {
            ZVAL_TEMP_GC(list, varZval);
        } else {
            do {
                i = 0;
                while (i < list->count) {
                    if (list->zval[i] == varZval) {
                        list->zval[i] = list->zval[list->count - 1];
                        list->count--;
                        break;
                    }
                    i++;
                }
                list = list->next;
            } while (list);
        }
    }

    switch (varZval->type) {
        case ZVAL_TYPE_STRING:
            if (varZval->value.str.len) {
                efree(varZval->value.str.val);
            }
            break;
        default:
            break;
    }
    freeConvertionCacheBuffer(varZval);
    efree(varZval);
}

zval * __attribute((fastcall)) ZVAL_COPY(zvallist *list, zval *oldzval) {
    zval *newzval;
    if (oldzval == NULL) {
        return NULL;
    }
    newzval = ZVAL_INIT(list);
    memcpy(newzval, oldzval, sizeof (zval));
    newzval->is_ref = 0;
    newzval->refcount = 1;
    if (newzval->type == ZVAL_TYPE_STRING) {
        newzval->value.str.len = oldzval->value.str.len;
        if (newzval->value.str.len) {
            newzval->value.str.val = emalloc(oldzval->value.str.len);
            memcpy(newzval->value.str.val, oldzval->value.str.val, oldzval->value.str.len);
        }
    }
    if (newzval->_convertion_cache_type == ZVAL_TYPE_STRING) {
        newzval->_convertion_cache.str.len = oldzval->_convertion_cache.str.len;
        if (newzval->_convertion_cache.str.len) {
            newzval->_convertion_cache.str.val = emalloc(oldzval->_convertion_cache.str.len);
            memcpy(newzval->_convertion_cache.str.val, oldzval->_convertion_cache.str.val, oldzval->_convertion_cache.str.len);
        }
    }
    return newzval;
}

zval * __attribute((fastcall)) ZVAL_COPY_ON_WRITE(zvallist *list, zval *oldzval) {
    zval *newzval;
    newzval = ZVAL_COPY(list, oldzval);
    ZVAL_GC(list, oldzval);
    return newzval;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_INTEGER(zvallist *list, zval *zval, long val) {
    if (!zval) {
        zval = ZVAL_INIT(list);
    }
    freeConvertionCacheBuffer(zval);
    if (!zval->is_ref) {
        if (zval->refcount > 1) {
            ZVAL_GC(list, zval);
            zval = ZVAL_INIT(list);
        }
        zval->refcount = 1;
    }
    zval->type = ZVAL_TYPE_INTEGER;
    zval->value.lval = val;
    return zval;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_BOOLEAN(zvallist *list, zval *varZval, long val) {
    zval *output;
    output = ZVAL_ASSIGN_INTEGER(list, varZval, (val == 0 ? 0 : 1));
    output->type = ZVAL_TYPE_BOOLEAN;
    return output;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_DOUBLE(zvallist *list, zval *zval, double val) {
    if (!zval) {
        zval = ZVAL_INIT(list);
    }
    freeConvertionCacheBuffer(zval);
    if (!zval->is_ref) {
        if (zval->refcount > 1) {
            ZVAL_GC(list, zval);
            zval = ZVAL_INIT(list);
        }
        zval->refcount = 1;
    }
    zval->refcount = 1;
    zval->type = ZVAL_TYPE_DOUBLE;
    zval->value.dval = val;
    return zval;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_STRING(zvallist *list, zval *zval, int len, char *val) {
    if (!zval) {
        zval = ZVAL_INIT(list);
    }
    freeConvertionCacheBuffer(zval);
    if (!zval->is_ref) {
        if (zval->refcount > 1) {
            ZVAL_GC(list, zval);
            zval = ZVAL_INIT(list);
        }
        zval->refcount = 1;
    }

    if (zval->type == ZVAL_TYPE_STRING && zval->value.str.len) {
        efree(zval->value.str.val);
    }

    zval->refcount = 1;
    zval->type = ZVAL_TYPE_STRING;
    zval->value.str.len = len;
    if (zval->value.str.len) {
        zval->value.str.val = emalloc(len);
        memcpy(zval->value.str.val, val, len);
    }

    return zval;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_ZVAL(zvallist *list, zval *zval1, zval *zval2) {
    if (zval1) {
        ZVAL_GC(list, zval1);
    }

    if (zval2->is_ref) {
        //need copy on write
        return ZVAL_COPY(list, zval2);
    }

    //inc ref_count
    zval2->refcount++;
    return zval2;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_CONCAT_STRING(zvallist *list, zval *zval, int len, char *val) {
    int newlen;
    char *newval;
    if (!zval) {
        zval = ZVAL_INIT(list);
    }
    if (!zval->is_ref && zval->refcount > 1)
        zval = ZVAL_COPY_ON_WRITE(list, zval);
    zval->refcount = 1;
    ZVAL_CONVERT_STRING(zval);
    newlen = zval->value.str.len + len;
    newval = emalloc(newlen);
    if (zval->value.str.len) {
        memcpy(newval, zval->value.str.val, zval->value.str.len);
    }
    if (len) {
        memcpy(&newval[zval->value.str.len], val, len);
    }
    if (zval->value.str.len) {
        efree(zval->value.str.val);
    }
    zval->value.str.val = newval;
    zval->value.str.len = newlen;
    return zval;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_CONCAT_ZVAL(zvallist *list, zval *zval1, zval *zval2) {
    char *tmpString;
    int tmpLen;
    int newlen;
    char *newval;
    if (!zval1) {
        zval1 = ZVAL_INIT(list);
    }
    if (zval1->refcount > 1)
        zval1 = ZVAL_COPY_ON_WRITE(list, zval1);
    zval1->refcount = 1;

    ZVAL_CONVERT_STRING(zval1);
    switch (zval2->type) {
        case ZVAL_TYPE_BOOLEAN:
        case ZVAL_TYPE_INTEGER:
        case ZVAL_TYPE_DOUBLE:
            ZVAL_STRING_VALUE(zval2, &tmpLen, &tmpString);
            newlen = zval1->value.str.len + tmpLen;
            if (newlen) {
                newval = emalloc(newlen);
            }
            if (zval1->value.str.len) {
                memcpy(newval, zval1->value.str.val, zval1->value.str.len);
            }
            if (tmpLen) {
                memcpy(&newval[zval1->value.str.len], tmpString, tmpLen);
            }
            break;
        case ZVAL_TYPE_STRING:
            newlen = zval1->value.str.len + zval2->value.str.len;
            if (newlen) {
                newval = emalloc(newlen);
            }
            if (zval1->value.str.len) {
                memcpy(newval, zval1->value.str.val, zval1->value.str.len);
            }
            if (zval2->value.str.len) {
                memcpy(&newval[zval1->value.str.len], zval2->value.str.val, zval2->value.str.len);
            }
            break;
        case ZVAL_TYPE_NULL:
        default:
            break;
    }

    if (zval1->value.str.len) {
        efree(zval1->value.str.val);
    }

    zval1->value.str.val = newval;
    zval1->value.str.len = newlen;
    return zval1;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_REF(zvallist *list, zval *zval) {
    char *newval;
    if (!zval) {
        return NULL;
    }
    if (!zval->is_ref && zval->refcount > 1) { //Copy on Write
        switch (zval->type) {
            case ZVAL_TYPE_BOOLEAN:
            case ZVAL_TYPE_INTEGER:
                zval = ZVAL_ASSIGN_INTEGER(list, zval, zval->value.lval);
                break;
            case ZVAL_TYPE_DOUBLE:
                zval = ZVAL_ASSIGN_DOUBLE(list, zval, zval->value.dval);
                break;
            case ZVAL_TYPE_STRING:
                zval = ZVAL_ASSIGN_STRING(list, zval, zval->value.str.len, zval->value.str.val);
                break;
            case ZVAL_TYPE_NULL:
            default:
                break;
        }
    }
    zval->is_ref = 1;
    zval->refcount++;
    return zval;
}

void __attribute((fastcall)) ZVAL_STRING_VALUE(zval *zval, int *len, char **str) {
    int buffersize;
    if (zval == NULL) {
        *len = 0;
        return;
    }
    if (len == NULL || str == NULL) {
        str = &zval->_convertion_cache.str.val;
        len = &zval->_convertion_cache.str.len;
    }
    if (zval->_convertion_cache_type == ZVAL_TYPE_STRING) {
        *str = zval->_convertion_cache.str.val;
        *len = zval->_convertion_cache.str.len;
        return;
    }
    switch (zval->type) {
        case ZVAL_TYPE_BOOLEAN:
        case ZVAL_TYPE_INTEGER:
            freeConvertionCacheBuffer(zval);
            zval->_convertion_cache_type = ZVAL_TYPE_STRING;
            buffersize = (sizeof (long) *8 / 3) + 1 + 1;
            zval->_convertion_cache.str.val = emalloc(buffersize);
            zval->_convertion_cache.str.len = snprintf(zval->_convertion_cache.str.val, buffersize, "%ld", zval->value.lval);
            *str = zval->_convertion_cache.str.val;
            *len = zval->_convertion_cache.str.len;
            break;
        case ZVAL_TYPE_DOUBLE:
            freeConvertionCacheBuffer(zval);
            zval->_convertion_cache_type = ZVAL_TYPE_STRING;
            buffersize = 64;
            zval->_convertion_cache.str.val = emalloc(buffersize);
            php_gcvt(zval->value.dval, DTOA_DISPLAY_DIGITS, '.', 'e', zval->_convertion_cache.str.val);
            zval->_convertion_cache.str.len = strlen(zval->_convertion_cache.str.val);
            *len = zval->_convertion_cache.str.len;
            *str = zval->_convertion_cache.str.val;
            break;
        case ZVAL_TYPE_STRING:
            *str = zval->value.str.val;
            *len = zval->value.str.len;
            return;
        default:
            break;
    }
}

void __attribute((fastcall)) ZVAL_CONVERT_STRING(zval *zval) {
    int len;
    char * val;
    char oldType;
    zvalue_value oldValue;
    if (zval == NULL) {
        return;
    }
    if (zval->type == ZVAL_TYPE_STRING) {
        return;
    }
    oldType = zval->type;
    memcpy(&oldValue, &zval->value, sizeof (zvalue_value));
    ZVAL_STRING_VALUE(zval, &len, &val);
    zval->type = ZVAL_TYPE_STRING;
    zval->value.str.val = val;
    zval->value.str.len = len;
    zval->_convertion_cache_type = oldType;
    memcpy(&zval->_convertion_cache, &oldValue, sizeof (zvalue_value));
}

long __attribute((fastcall)) ZVAL_INTEGER_VALUE(zval *zval) {
    char *tmpBuffer;
    long returnVal;
    if (zval == NULL) {
        return 0;
    }
    if (zval->_convertion_cache_type == ZVAL_TYPE_INTEGER) {
        return zval->_convertion_cache.lval;
    }
    switch (zval->type) {
        case ZVAL_TYPE_BOOLEAN:
        case ZVAL_TYPE_INTEGER:
            return zval->value.lval;
        case ZVAL_TYPE_DOUBLE:
            freeConvertionCacheBuffer(zval);
            zval->_convertion_cache_type = ZVAL_TYPE_INTEGER;
            zval->_convertion_cache.lval = (long) zval->value.dval;
            return zval->_convertion_cache.lval;
        case ZVAL_TYPE_STRING:
            freeConvertionCacheBuffer(zval);
            tmpBuffer = emalloc(zval->value.str.len + 1);
            memcpy(tmpBuffer, zval->value.str.val, zval->value.str.len);
            tmpBuffer[zval->value.str.len] = '\0';
            returnVal = atol(tmpBuffer);
            efree(tmpBuffer);
            zval->_convertion_cache_type = ZVAL_TYPE_INTEGER;
            zval->_convertion_cache.lval = returnVal;
            return returnVal;
        default:
            return 0;
    }
}

void __attribute((fastcall)) ZVAL_CONVERT_INTEGER(zval *zval) {
    char oldType;
    zvalue_value oldValue;
    if (zval == NULL) {
        return;
    }
    if (zval->type == ZVAL_TYPE_INTEGER) {
        return;
    }
    oldType = zval->type;
    memcpy(&oldValue, &zval->value, sizeof (zvalue_value));
    zval->value.lval = ZVAL_INTEGER_VALUE(zval);
    zval->type = ZVAL_TYPE_INTEGER;
    zval->_convertion_cache_type = oldType;
    memcpy(&zval->_convertion_cache, &oldValue, sizeof (zvalue_value));
}

double __attribute((fastcall)) ZVAL_DOUBLE_VALUE(zval *zval) {
    char *tmpBuffer;
    double returnVal;
    if (zval == NULL) {
        return 0;
    }
    if (zval->_convertion_cache_type == ZVAL_TYPE_DOUBLE) {
        return zval->_convertion_cache.dval;
    }
    switch (zval->type) {
        case ZVAL_TYPE_BOOLEAN:
        case ZVAL_TYPE_INTEGER:
            freeConvertionCacheBuffer(zval);
            zval->_convertion_cache_type = ZVAL_TYPE_DOUBLE;
            zval->_convertion_cache.dval = (double) zval->value.lval;
            return zval->_convertion_cache.dval;
        case ZVAL_TYPE_DOUBLE:
            return zval->value.dval;
        case ZVAL_TYPE_STRING:
            freeConvertionCacheBuffer(zval);
            tmpBuffer = emalloc(zval->value.str.len + 1);
            memcpy(tmpBuffer, zval->value.str.val, zval->value.str.len);
            tmpBuffer[zval->value.str.len] = '\0';
            returnVal = strtod(tmpBuffer, 0);
            efree(tmpBuffer);
            zval->_convertion_cache_type = ZVAL_TYPE_DOUBLE;
            zval->_convertion_cache.dval = returnVal;
            return returnVal;
        default:
            return 0;
    }
}

void __attribute((fastcall)) ZVAL_CONVERT_DOUBLE(zval *zval) {
    char oldType;
    zvalue_value oldValue;
    if (zval == NULL) {
        return;
    }
    if (zval->type == ZVAL_TYPE_DOUBLE) {
        return;
    }
    oldType = zval->type;
    memcpy(&oldValue, &zval->value, sizeof (zvalue_value));
    zval->value.dval = ZVAL_DOUBLE_VALUE(zval);
    zval->type = ZVAL_TYPE_DOUBLE;
    zval->_convertion_cache_type = oldType;
    memcpy(&zval->_convertion_cache, &oldValue, sizeof (zvalue_value));
}

int __attribute((fastcall)) is_number(int len, char *val) {
    char dotAssigned = 0;
    char exponentAssigned = 0;
    for (int i = 0; i < len; i++) {
        if (val[i] < '0' || val[i] > '9') {
            if ((val[i] == 'e' || val[i] == 'E') && exponentAssigned == 0) {
                exponentAssigned = 1;
                continue;
            }
            if (val[i] == '+' || val[i] == '-') {
                continue;
            }
            if (val[i] == '.' && dotAssigned == 0) {
                dotAssigned = 1;
                continue;
            }
            return 0;
        }
    }
    return 1;
}

int __attribute((fastcall)) ZVAL_TYPE_GUESS(zval *zval) {
    if (zval->type == ZVAL_TYPE_STRING && (!is_number(zval->value.str.len, zval->value.str.val))) {
        return ZVAL_TYPE_STRING;
    }
    return ZVAL_TYPE_GUESS_NUMBER(zval);
}

int __attribute((fastcall)) ZVAL_TYPE_GUESS_NUMBER(zval *zval) {
    double doubleVal;
    long integerVal;
    doubleVal = ZVAL_DOUBLE_VALUE(zval);
    integerVal = ZVAL_INTEGER_VALUE(zval);
    if (doubleVal == (double) integerVal) {
        return ZVAL_TYPE_INTEGER;
    }
    return ZVAL_TYPE_DOUBLE;
}

int __attribute((fastcall)) ZVAL_TYPE_CAST_SINGLE(zval *zvalop1, type_cast *value_op1) {
    if (ZVAL_TYPE_GUESS(zvalop1) == ZVAL_TYPE_STRING) {
        return ZVAL_TYPE_STRING;
    }
    return ZVAL_TYPE_CAST_NUMBER_SINGLE(zvalop1, value_op1);
}

int __attribute((fastcall)) ZVAL_TYPE_CAST_NUMBER_SINGLE(zval *zvalop1, type_cast *value_op1) {
    if (ZVAL_TYPE_GUESS_NUMBER(zvalop1) == ZVAL_TYPE_DOUBLE) { //double type
        value_op1->dval = ZVAL_DOUBLE_VALUE(zvalop1);
        return ZVAL_TYPE_DOUBLE;
    }
    value_op1->lval = ZVAL_INTEGER_VALUE(zvalop1);
    return ZVAL_TYPE_INTEGER;
}

int __attribute((fastcall)) ZVAL_TYPE_CAST(zval *zvalop1, zval *zvalop2, type_cast *value_op1, type_cast *value_op2) {
    if (ZVAL_TYPE_GUESS(zvalop1) == ZVAL_TYPE_STRING || ZVAL_TYPE_GUESS(zvalop2) == ZVAL_TYPE_STRING) {
        return ZVAL_TYPE_STRING;
    }
    return ZVAL_TYPE_CAST_NUMBER(zvalop1, zvalop2, value_op1, value_op2);
}

int __attribute((fastcall)) ZVAL_TYPE_CAST_NUMBER(zval *zvalop1, zval *zvalop2, type_cast *value_op1, type_cast *value_op2) {
    int i, targetType = 0;
    zval * list[2];
    list[0] = zvalop1;
    list[1] = zvalop2;
    for (i = 0; i < 2; i++) {
        if (list[i] != NULL) {
            switch (list[i]->type) {
                case ZVAL_TYPE_STRING:
                    if (ZVAL_TYPE_GUESS_NUMBER(list[i]) == ZVAL_TYPE_INTEGER) {
                        targetType |= 0;
                    } else {
                        targetType |= 1;
                    }
                    break;
                case ZVAL_TYPE_DOUBLE:
                    targetType |= 1;
                    break;
                case ZVAL_TYPE_BOOLEAN:
                case ZVAL_TYPE_INTEGER:
                    targetType |= 0;
                    break;
            }
        }
    }
    if (targetType) { //double type
        value_op1->dval = ZVAL_DOUBLE_VALUE(zvalop1);
        value_op2->dval = ZVAL_DOUBLE_VALUE(zvalop2);
        return ZVAL_TYPE_DOUBLE;
    }
    value_op1->lval = ZVAL_INTEGER_VALUE(zvalop1);
    value_op2->lval = ZVAL_INTEGER_VALUE(zvalop2);
    return ZVAL_TYPE_INTEGER;
}

long __attribute((fastcall)) ZVAL_EQUAL_STRING(zval *zvalop1, int len, char *val) {
    int op1len;
    char *op1val;
    if (zvalop1->type != ZVAL_TYPE_STRING) {
        return 0;
    }
    ZVAL_STRING_VALUE(zvalop1, &op1len, &op1val);
    if (op1len != len) {
        return 0;
    }
    return (strncmp(op1val, val, op1len) == 0);
}

long __attribute((fastcall)) ZVAL_EQUAL(zval *zvalop1, zval *zvalop2) {
    int op1len, op2len;
    char *op1val, *op2val;
    type_cast value_op1, value_op2;
    if (ZVAL_TYPE_GUESS(zvalop1) == ZVAL_TYPE_STRING || ZVAL_TYPE_GUESS(zvalop2) == ZVAL_TYPE_STRING) {
        ZVAL_STRING_VALUE(zvalop1, &op1len, &op1val);
        ZVAL_STRING_VALUE(zvalop2, &op2len, &op2val);
        if (op1len != op2len) {
            return 0;
        }
        return (strncmp(op1val, op2val, op1len) == 0);
    }
    switch (ZVAL_TYPE_CAST_NUMBER(zvalop1, zvalop2, &value_op1, &value_op2)) {
        case ZVAL_TYPE_DOUBLE:
            return (value_op1.dval == value_op2.dval);
        default:
            return (value_op1.lval == value_op2.lval);
    }
}

long __attribute((fastcall)) ZVAL_EQUAL_EXACT(zval *zvalop1, zval *zvalop2) {
    if (zvalop1->type != zvalop2->type) {
        return 0;
    }
    switch (zvalop1->type) {
        case ZVAL_TYPE_STRING:
            if (zvalop1->value.str.len != zvalop2->value.str.len) {
                return 0;
            }
            return (strncmp(zvalop1->value.str.val, zvalop2->value.str.val, zvalop1->value.str.len) == 0);
        case ZVAL_TYPE_BOOLEAN:
        case ZVAL_TYPE_INTEGER:
            return (zvalop1->value.lval == zvalop2->value.lval);
        case ZVAL_TYPE_DOUBLE:
            return (zvalop1->value.dval == zvalop2->value.dval);
        default:
            return 0;
    }
}

long __attribute((fastcall)) ZVAL_TEST_NULL(zval *zvalop1) {

    if ((!zvalop1) || (zvalop1->type == ZVAL_TYPE_NULL)) {
        return TRUE;
    }

    return FALSE;
}

long __attribute((fastcall)) ZVAL_TEST_FALSE(zval *zvalop1) {
    type_cast value_op1;

    if (ZVAL_TEST_NULL(zvalop1)) {
        return TRUE;
    }

    if ((zvalop1->type == ZVAL_TYPE_STRING) && (!is_number(zvalop1->value.str.len, zvalop1->value.str.val)) ) {
        return FALSE;
    }

    switch(ZVAL_TYPE_CAST_NUMBER_SINGLE(zvalop1,&value_op1)){
        case ZVAL_TYPE_DOUBLE:
            return value_op1.dval==0;
        case ZVAL_TYPE_INTEGER:
            return value_op1.lval==0;
    }

    return TRUE;
}