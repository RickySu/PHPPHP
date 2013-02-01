#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include "h/ZVAL.h"
#include "h/ZVAL_LIST.h"
#include "h/dtoa.h"

void __attribute((fastcall)) freeConvertionCacheBuffer(zval *zval) {
    if (zval->_convertion_cache_type == ZVAL_TYPE_STRING) {
        free(zval->_convertion_cache.str.val);
    }
}

zval * __attribute((fastcall)) ZVAL_INIT(zvallist *list) {
    zval * aZval;
    aZval = malloc(sizeof (zval));
    memset(aZval, 0, sizeof (zval));
    ZVAL_GC_REGISTER(list, aZval);
    aZval->refcount = 1;
    return aZval;
}

void __attribute((fastcall)) ZVAL_GC_REGISTER(zvallist *list, zval *zval) {
    if (list->count == list->len) {
        ZVAL_GC_REGISTER(list->next, zval);
        return;
    }
    list->zval[list->count++] = zval;
}

void __attribute((fastcall)) ZVAL_GC(zvallist *list, zval *zval) {
    int i;
    if (--zval->refcount) {
        if (zval->refcount == 1) {
            zval->is_ref = 0;
        }
        return;
    }

    if (list) {
        do {
            for (i = 0; i < list->count; i++) {
                if (list->zval[i] == zval) {
                    list->zval[i] = list->zval[list->count - 1];
                    list->count--;
                }
            }
            list = list->next;
        } while (list);
    }
    switch (zval->type) {
        case ZVAL_TYPE_STRING:
            if (zval->value.str.len) {
                free(zval->value.str.val);
            }
            break;
        default:
            break;
    }
    freeConvertionCacheBuffer(zval);
    free(zval);
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
            newzval->value.str.val = malloc(oldzval->value.str.len);
            memcpy(newzval->value.str.val, oldzval->value.str.val, oldzval->value.str.len);
        }
    }
    if (newzval->_convertion_cache_type == ZVAL_TYPE_STRING) {
        newzval->_convertion_cache.str.len = oldzval->_convertion_cache.str.len;
        if (newzval->_convertion_cache.str.len) {
            newzval->_convertion_cache.str.val = malloc(oldzval->_convertion_cache.str.len);
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

zval * __attribute((fastcall)) ZVAL_ASSIGN_DOUBLE(zvallist *list, zval *zval, double val) {
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
    if (!zval->is_ref) {
        if (zval->refcount > 1) {
            ZVAL_GC(list, zval);
            zval = ZVAL_INIT(list);
        }
        zval->refcount = 1;
    }
    zval->refcount = 1;
    zval->type = ZVAL_TYPE_STRING;
    if (zval->value.str.len) {
        free(zval->value.str.val);
    }
    zval->value.str.len = len;
    if (zval->value.str.len) {
        zval->value.str.val = malloc(len);
        memcpy(zval->value.str.val, val, len);
    }
    return zval;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_CONCAT_STRING(zvallist *list, zval *zval, int len, char *val) {
    int newlen;
    char *newval;
    if (!zval->is_ref && zval->refcount > 1)
        zval = ZVAL_COPY_ON_WRITE(list, zval);
    zval->refcount = 1;
    ZVAL_CONVERT_STRING(zval);
    newlen = zval->value.str.len + len;
    newval = malloc(newlen);
    if (zval->value.str.len) {
        memcpy(newval, zval->value.str.val, zval->value.str.len);
    }
    if (len) {
        memcpy(&newval[zval->value.str.len], val, len);
    }
    if (zval->value.str.len) {
        free(zval->value.str.val);
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
                newval = malloc(newlen);
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
                newval = malloc(newlen);
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
    if (zval1->value.str.val) {
        free(zval1->value.str.val);
    }
    zval1->value.str.val = newval;
    zval1->value.str.len = newlen;
    return zval1;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_REF(zvallist *list, zval *zval) {
    char *newval;
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
            zval->_convertion_cache.str.val = malloc(buffersize);
            zval->_convertion_cache.str.len = snprintf(zval->_convertion_cache.str.val, buffersize, "%ld", zval->value.lval);
            *str = zval->_convertion_cache.str.val;
            *len = zval->_convertion_cache.str.len;
            break;
        case ZVAL_TYPE_DOUBLE:
            freeConvertionCacheBuffer(zval);
            zval->_convertion_cache_type = ZVAL_TYPE_STRING;
            buffersize = 64;
            zval->_convertion_cache.str.val = malloc(buffersize);
            php_gcvt(zval->value.dval, DTOA_DISPLAY_DIGITS, '.', 'e', zval->_convertion_cache.str.val);
            zval->_convertion_cache.str.len = strlen(zval->_convertion_cache.str.val);
            *len = zval->_convertion_cache.str.len;
            *str = zval->_convertion_cache.str.val;
            break;
        case ZVAL_TYPE_STRING:
            *str = zval->value.str.val;
            *len = zval->value.str.len;
            return;
        case ZVAL_TYPE_NULL:
            *len = 0;
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
    zval->type = ZVAL_TYPE_STRING;
}

long __attribute((fastcall)) ZVAL_INTEGER_VALUE(zval *zval) {
    char *tmpBuffer;
    long returnVal;
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
            tmpBuffer = malloc(zval->value.str.len + 1);
            memcpy(tmpBuffer, zval->value.str.val, zval->value.str.len);
            tmpBuffer[zval->value.str.len] = '\0';
            returnVal = atol(tmpBuffer);
            free(tmpBuffer);
            zval->_convertion_cache_type = ZVAL_TYPE_INTEGER;
            zval->_convertion_cache.lval = returnVal;
            return returnVal;
        case ZVAL_TYPE_NULL:
            return 0;
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
    zval->type = ZVAL_TYPE_INTEGER;
    zval->value.lval = ZVAL_INTEGER_VALUE(zval);
    zval->_convertion_cache_type = oldType;
    memcpy(&zval->_convertion_cache, &oldValue, sizeof (zvalue_value));
}

double __attribute((fastcall)) ZVAL_DOUBLE_VALUE(zval *zval) {
    char *tmpBuffer;
    double returnVal;
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
            tmpBuffer = malloc(zval->value.str.len + 1);
            memcpy(tmpBuffer, zval->value.str.val, zval->value.str.len);
            tmpBuffer[zval->value.str.len] = '\0';
            returnVal = strtod(tmpBuffer, 0);
            free(tmpBuffer);
            zval->_convertion_cache_type = ZVAL_TYPE_DOUBLE;
            zval->_convertion_cache.dval = returnVal;
            return returnVal;
        case ZVAL_TYPE_NULL:
            return 0;
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
    zval->type = ZVAL_TYPE_DOUBLE;
    zval->value.dval = ZVAL_DOUBLE_VALUE(zval);
    zval->_convertion_cache_type = oldType;
    memcpy(&zval->_convertion_cache, &oldValue, sizeof (zvalue_value));
}

int __attribute((fastcall)) ZVAL_TYPE_GUESS(zval *zval) {
    double doubleVal;
    long integerVal;
    if (zval->_convertion_cache_type == ZVAL_TYPE_DOUBLE) {
        return ZVAL_TYPE_DOUBLE;
    }
    if (zval->_convertion_cache_type == ZVAL_TYPE_INTEGER) {
        return ZVAL_TYPE_INTEGER;
    }
    doubleVal = ZVAL_DOUBLE_VALUE(zval);
    integerVal = ZVAL_INTEGER_VALUE(zval);
    if (doubleVal == (double) integerVal) {
        return ZVAL_TYPE_INTEGER;
    }
    return ZVAL_TYPE_DOUBLE;
}

int __attribute((fastcall)) ZVAL_TYPE_CAST_SINGLE(int type, zval *zvalop1, type_cast *value_op1) {
    int targetType;
    if (type == ZVAL_TYPE_DOUBLE) {
        targetType = 1;
    } else {
        targetType = 0;
    }
    switch (zvalop1->type) {
        case ZVAL_TYPE_STRING:
            if (ZVAL_TYPE_GUESS(zvalop1) == ZVAL_TYPE_INTEGER) {
                targetType |= 0;
            } else {
                targetType |= 1;
            }
            break;
        case ZVAL_TYPE_DOUBLE:
            targetType |= 1;
            break;
        case ZVAL_TYPE_BOOLEAN:
        case ZVAL_TYPE_NULL:
        case ZVAL_TYPE_INTEGER:
            targetType |= 0;
            break;
    }
    if (targetType) { //double type
        value_op1->dval = ZVAL_DOUBLE_VALUE(zvalop1);
        return ZVAL_TYPE_DOUBLE;
    }
    value_op1->lval = ZVAL_INTEGER_VALUE(zvalop1);
    return ZVAL_TYPE_INTEGER;
}

int __attribute((fastcall)) ZVAL_TYPE_CAST(zval *zvalop1, zval *zvalop2, type_cast *value_op1, type_cast *value_op2) {
    int i, targetType = 0;
    zval * list[2];
    list[0] = zvalop1;
    list[1] = zvalop2;
    for (i = 0; i < 2; i++) {
        switch (list[i]->type) {
            case ZVAL_TYPE_STRING:
                if (ZVAL_TYPE_GUESS(list[i]) == ZVAL_TYPE_INTEGER) {
                    targetType |= 0;
                } else {
                    targetType |= 1;
                }
                break;
            case ZVAL_TYPE_DOUBLE:
                targetType |= 1;
                break;
            case ZVAL_TYPE_BOOLEAN:
            case ZVAL_TYPE_NULL:
            case ZVAL_TYPE_INTEGER:
                targetType |= 0;
                break;
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
