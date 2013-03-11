#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include "h/ZVAL.h"

uint zvalcount = 0;
static inline zval* prepareForAssign(zval *varZval);
static inline zval *prepareForArrayAssign(zval *dstZval);

PHPLLVMAPI void freeConvertionCacheBuffer(zval *zval) {
    if (zval->_convertion_cache_type == ZVAL_TYPE_STRING) {
        efree(zval->_convertion_cache.str.val);
        zval->_convertion_cache.str.len = 0;
    }
    zval->_convertion_cache_type = ZVAL_TYPE_NULL;
}

static inline zval *prepareForArrayAssign(zval *dstZval) {
    if (!dstZval) {
        dstZval = ZVAL_INIT();
    }

    if (!dstZval->is_ref && dstZval->refcount > 1) {
        zval *oldZval;
        oldZval = dstZval;
        dstZval = ZVAL_INIT();
        zval_copy_content(dstZval, oldZval);
        ZVAL_GC(oldZval);
        dstZval->refcount = 1;
        dstZval->is_ref = 0;
    }

    if (dstZval->type != ZVAL_TYPE_ARRAY) {
        emptyZval(dstZval);
        ZVAL_INIT_ARRAY(dstZval);
    }

    return dstZval;
}

static inline zval* prepareForAssign(zval *varZval) {
    if (!varZval) {
        return ZVAL_INIT();
    }

    if (!varZval->is_ref && varZval->refcount > 1) {
        ZVAL_GC(varZval);
        return ZVAL_INIT();
    }

    emptyZval(varZval);
    return varZval;
}

zval * ZVAL_INIT() {
    zval * aZval;
    zvalcount++;
    aZval = ecalloc(1, sizeof (zval));
    aZval->refcount = 1;
    return aZval;
}

FASTCC void hashtable_zval_gc_dtor(void *pDest) {
    ZVAL_GC((zval*) pDest);
}

PHPLLVMAPI zval *ZVAL_INIT_ARRAY(zval *aZval) {
    if (!aZval) {
        aZval = ZVAL_INIT();
    }
    if (aZval->type != ZVAL_TYPE_ARRAY) {
        aZval->type = ZVAL_TYPE_ARRAY;
        aZval->hashtable = emalloc(sizeof (HashTable));
        hash_init(aZval->hashtable, DEFAULT_HASHTABLE_BUCKET_SIZE, &hashtable_zval_gc_dtor);
    }
    return aZval;
}

PHPLLVMAPI void emptyZval(zval *varZval) {
    switch (varZval->type) {
        case ZVAL_TYPE_STRING:
            if (varZval->value.str.len) {
                efree(varZval->value.str.val);
            }
            break;
        case ZVAL_TYPE_ARRAY:
            hash_destroy(varZval->hashtable);
            efree(varZval->hashtable);
            break;
    }
    varZval->hashtable = NULL;
    freeConvertionCacheBuffer(varZval);
}

PHPLLVMAPI void ZVAL_GC(zval *varZval) {
    if (!varZval) {
        return;
    }
    if (--varZval->refcount > 0) {
        if (varZval->refcount == 1) {
            varZval->is_ref = 0;
        }
        if (varZval->hashtable) {
            gc_pool_add(varZval);
        }
        return;
    }
    zval_gc_real(varZval);
}

FASTCC void zval_gc_real(zval *varZval) {
    gc_pool_remove(varZval);
    emptyZval(varZval);
    efree(varZval);
    zvalcount--;
}

PHPLLVMAPI void zval_copy_content(zval *dstZval, zval *srcZval) {
    memcpy(dstZval, srcZval, sizeof (zval));
    if (dstZval->type == ZVAL_TYPE_STRING) {
        if (dstZval->value.str.len) {
            dstZval->value.str.val = emalloc(srcZval->value.str.len);
            memcpy(dstZval->value.str.val, srcZval->value.str.val, srcZval->value.str.len);
        }
    }
    if (dstZval->_convertion_cache_type == ZVAL_TYPE_STRING) {
        if (dstZval->_convertion_cache.str.len) {
            dstZval->_convertion_cache.str.val = emalloc(srcZval->_convertion_cache.str.len);
            memcpy(dstZval->_convertion_cache.str.val, srcZval->_convertion_cache.str.val, srcZval->_convertion_cache.str.len);
        }
    }
    if (srcZval->hashtable) {
        dstZval->hashtable = emalloc(sizeof (HashTable));
        hash_init(dstZval->hashtable, DEFAULT_HASHTABLE_BUCKET_SIZE, &hashtable_zval_gc_dtor);
        hash_copy(dstZval->hashtable, srcZval->hashtable);
    }
}

PHPLLVMAPI zval * ZVAL_COPY(zval *srcZval) {
    zval *dstZval;
    if (srcZval == NULL) {
        return NULL;
    }
    dstZval = ZVAL_INIT();
    zval_copy_content(dstZval, srcZval);
    dstZval->is_ref = 0;
    dstZval->refcount = 1;
    return dstZval;
}

PHPLLVMAPI zval * ZVAL_COPY_ON_WRITE(zval *srcZval) {
    zval *dstZval;
    dstZval = ZVAL_COPY(srcZval);
    ZVAL_GC(srcZval);
    return dstZval;
}

PHPLLVMAPI zval * ZVAL_ASSIGN_INTEGER(zval *dstZval, long val) {
    dstZval = prepareForAssign(dstZval);
    dstZval->type = ZVAL_TYPE_INTEGER;
    dstZval->value.lval = val;
    return dstZval;
}

PHPLLVMAPI zval * ZVAL_ASSIGN_BOOLEAN(zval *dstZval, long val) {
    dstZval = ZVAL_ASSIGN_INTEGER(dstZval, (val == 0 ? 0 : 1));
    dstZval->type = ZVAL_TYPE_BOOLEAN;
    return dstZval;
}

PHPLLVMAPI zval * ZVAL_ASSIGN_DOUBLE(zval *dstZval, double val) {
    dstZval = prepareForAssign(dstZval);
    dstZval->type = ZVAL_TYPE_DOUBLE;
    dstZval->value.dval = val;
    return dstZval;
}

PHPLLVMAPI zval * ZVAL_ASSIGN_STRING(zval *dstZval, uint len, char *val) {
    dstZval = prepareForAssign(dstZval);
    dstZval->type = ZVAL_TYPE_STRING;
    dstZval->value.str.len = len;
    if (dstZval->value.str.len) {
        dstZval->value.str.val = emalloc(len);
        memcpy(dstZval->value.str.val, val, len);
    }

    return dstZval;
}

PHPLLVMAPI zval *ZVAL_ASSIGN_ARRAY_ZVAL_ELEMENT(zval *dstZval, zval *srcZval, zval *keyZval) {
    ulong index = 0;
    uint nKeyLength = 0;
    char arKey[64];
    char *arKeyPtr = &arKey[0];
    dstZval = prepareForArrayAssign(dstZval);
    switch (keyZval->type) {
        case ZVAL_TYPE_BOOLEAN:
            index = keyZval->value.lval;
            break;
        case ZVAL_TYPE_INTEGER:
            if (keyZval->value.lval < 0) {
                nKeyLength = snprintf(arKey, sizeof (arKey), "%ld", keyZval->value.lval);
                break;
            }
            index = keyZval->value.lval;
            break;
        case ZVAL_TYPE_DOUBLE:
            if (keyZval->value.dval < 0) {
                nKeyLength = snprintf(arKey, sizeof (arKey), "%ld", (long) keyZval->value.dval);
                break;
            }
            index = (ulong) keyZval->value.dval;
            break;
        case ZVAL_TYPE_STRING:
            if (is_number(keyZval->value.str.len, keyZval->value.str.val)) {
                long intValue = ZVAL_INTEGER_VALUE(keyZval);
                if (intValue < 0) {
                    nKeyLength = snprintf(arKey, sizeof (arKey), "%ld", intValue);
                    break;
                }
                index = (ulong) intValue;
                break;
            }
            arKeyPtr = keyZval->value.str.val;
            nKeyLength = keyZval->value.str.len;
            break;
        default:
            break;

    }

    if (nKeyLength) {
        hash_add_or_update_string_index(dstZval->hashtable, srcZval, nKeyLength, arKeyPtr);
    }
    if (index) {
        hash_add_or_update_index(dstZval->hashtable, srcZval, index);
    }
    if (index == 0 && nKeyLength == 0) {
        hash_add_or_update_index(dstZval->hashtable, srcZval, index);
    }
    return dstZval;
}

PHPLLVMAPI zval *ZVAL_ASSIGN_ARRAY_STRING_ELEMENT(zval *dstZval, zval *srcZval, uint nKeyLength, char *arKey) {
    dstZval = prepareForArrayAssign(dstZval);
    hash_add_or_update_string_index(dstZval->hashtable, srcZval, nKeyLength, arKey);
    return dstZval;
}

PHPLLVMAPI zval *ZVAL_ASSIGN_ARRAY_INTEGER_ELEMENT(zval *dstZval, zval *srcZval, ulong index) {
    dstZval = prepareForArrayAssign(dstZval);
    hash_add_or_update_index(dstZval->hashtable, srcZval, index);
    return dstZval;
}

PHPLLVMAPI zval *ZVAL_ASSIGN_ARRAY_NEXT_ELEMENT(zval *dstZval, zval *srcZval) {
    dstZval = prepareForArrayAssign(dstZval);
    hash_add_next(dstZval->hashtable, srcZval);
    return dstZval;
}

PHPLLVMAPI zval *ZVAL_ASSIGN_ZVAL(zval *zval1, zval *zval2) {
    uint refcount;

    if (zval1 == zval2) {
        return zval1;
    }

    if (zval1 && zval1->is_ref) {
        //copy content
        refcount = zval1->refcount;
        zval_copy_content(zval1, zval2);
        zval1->is_ref = 1;
        return zval1;
    }

    if (zval2 == NULL) {
        ZVAL_GC(zval1);
        return zval2;
    }

    if (zval2->is_ref) {
        ZVAL_GC(zval1);
        return ZVAL_COPY(zval2);
    }

    //inc ref_count
    ZVAL_GC(zval1);
    zval2->refcount++;
    return zval2;
}

PHPLLVMAPI zval * ZVAL_ASSIGN_CONCAT_STRING(zval *zval, uint len, char *val) {
    uint newlen;
    char *newval;
    if (!zval) {
        zval = ZVAL_INIT();
    }
    if (!zval->is_ref && zval->refcount > 1)
        zval = ZVAL_COPY_ON_WRITE(zval);
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

PHPLLVMAPI zval * ZVAL_ASSIGN_CONCAT_ZVAL(zval *zval1, zval *zval2) {
    char *tmpString;
    uint tmpLen;
    uint newlen;
    char *newval;

    if (!zval2) {
        return zval1;
    }

    if (!zval1) {
        zval1 = ZVAL_INIT();
    }
    if (zval1->refcount > 1)
        zval1 = ZVAL_COPY_ON_WRITE(zval1);
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

PHPLLVMAPI zval * ZVAL_ASSIGN_REF(zval *srcZval) {
    zval *oldZval, *newZval;

    if (!srcZval) {
        return NULL;
    }

    if (srcZval->is_ref || (srcZval->refcount == 1)) {
        srcZval->is_ref = 1;
        srcZval->refcount++;
        return srcZval;
    }

    oldZval = srcZval;
    newZval = ZVAL_COPY(srcZval);
    if (newZval) {
        ZVAL_GC(oldZval);
        newZval->is_ref = 1;
        newZval->refcount++;
    }
    return newZval;
}

PHPLLVMAPI void ZVAL_STRING_VALUE(zval *zval, uint *len, char **str) {
    uint buffersize;
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

PHPLLVMAPI void ZVAL_CONVERT_STRING(zval * zval) {
    uint len;
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

PHPLLVMAPI long ZVAL_INTEGER_VALUE(zval * zval) {
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

PHPLLVMAPI void ZVAL_CONVERT_INTEGER(zval * zval) {
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

PHPLLVMAPI double ZVAL_DOUBLE_VALUE(zval * zval) {
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

PHPLLVMAPI void ZVAL_CONVERT_DOUBLE(zval * zval) {
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

PHPLLVMAPI int is_number(uint len, char *val) {
    char dotAssigned = 0;
    char exponentAssigned = 0;
    for (uint i = 0; i < len; i++) {
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

PHPLLVMAPI int ZVAL_TYPE_GUESS(zval * zval) {
    if (zval->type == ZVAL_TYPE_STRING && (!is_number(zval->value.str.len, zval->value.str.val))) {
        return ZVAL_TYPE_STRING;
    }
    return ZVAL_TYPE_GUESS_NUMBER(zval);
}

PHPLLVMAPI int ZVAL_TYPE_GUESS_NUMBER(zval * zval) {
    double doubleVal;
    long integerVal;
    doubleVal = ZVAL_DOUBLE_VALUE(zval);
    integerVal = ZVAL_INTEGER_VALUE(zval);
    if (doubleVal == (double) integerVal) {
        return ZVAL_TYPE_INTEGER;
    }
    return ZVAL_TYPE_DOUBLE;
}

PHPLLVMAPI int ZVAL_TYPE_CAST_SINGLE(zval *zvalop1, type_cast * value_op1) {
    if (ZVAL_TYPE_GUESS(zvalop1) == ZVAL_TYPE_STRING) {
        return ZVAL_TYPE_STRING;
    }
    return ZVAL_TYPE_CAST_NUMBER_SINGLE(zvalop1, value_op1);
}

PHPLLVMAPI int ZVAL_TYPE_CAST_NUMBER_SINGLE(zval *zvalop1, type_cast * value_op1) {
    if (ZVAL_TYPE_GUESS_NUMBER(zvalop1) == ZVAL_TYPE_DOUBLE) { //double type
        value_op1->dval = ZVAL_DOUBLE_VALUE(zvalop1);
        return ZVAL_TYPE_DOUBLE;
    }
    value_op1->lval = ZVAL_INTEGER_VALUE(zvalop1);
    return ZVAL_TYPE_INTEGER;
}

PHPLLVMAPI int ZVAL_TYPE_CAST(zval *zvalop1, zval *zvalop2, type_cast *value_op1, type_cast * value_op2) {
    if (ZVAL_TYPE_GUESS(zvalop1) == ZVAL_TYPE_STRING || ZVAL_TYPE_GUESS(zvalop2) == ZVAL_TYPE_STRING) {
        return ZVAL_TYPE_STRING;
    }
    return ZVAL_TYPE_CAST_NUMBER(zvalop1, zvalop2, value_op1, value_op2);
}

PHPLLVMAPI int ZVAL_TYPE_CAST_NUMBER(zval *zvalop1, zval *zvalop2, type_cast *value_op1, type_cast * value_op2) {
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

PHPLLVMAPI long ZVAL_EQUAL_STRING(zval *zvalop1, uint len, char *val) {
    uint op1len;
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

PHPLLVMAPI long ZVAL_EQUAL(zval *zvalop1, zval * zvalop2) {
    uint op1len, op2len;
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

PHPLLVMAPI long ZVAL_EQUAL_EXACT(zval *zvalop1, zval * zvalop2) {
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

PHPLLVMAPI long ZVAL_TEST_NULL(zval * zvalop1) {

    if ((!zvalop1) || (zvalop1->type == ZVAL_TYPE_NULL)) {
        return TRUE;
    }

    return FALSE;
}

PHPLLVMAPI long ZVAL_TEST_FALSE(zval * zvalop1) {
    type_cast value_op1;

    if (ZVAL_TEST_NULL(zvalop1)) {
        return TRUE;
    }

    if ((zvalop1->type == ZVAL_TYPE_STRING) && (!is_number(zvalop1->value.str.len, zvalop1->value.str.val))) {
        return FALSE;
    }

    switch (ZVAL_TYPE_CAST_NUMBER_SINGLE(zvalop1, &value_op1)) {
        case ZVAL_TYPE_DOUBLE:
            return value_op1.dval == 0;
        case ZVAL_TYPE_INTEGER:
            return value_op1.lval == 0;
    }

    return TRUE;
}

PHPLLVMAPI zval *ZVAL_FETCH_ARRAY_INTEGER_ELEMENT(zval *arrayZval, uint index, uint forWrite) {
    zval *returnZval;
    if (!arrayZval || arrayZval->type != ZVAL_TYPE_ARRAY) {
        return NULL;
    }
    returnZval = (zval *) hash_find_index(arrayZval->hashtable, index);
    if (forWrite && (!returnZval)) {
        zval *srcZval = ZVAL_INIT();
        hash_add_or_update_index(arrayZval->hashtable, srcZval, index);
        return srcZval;
    }
    return returnZval;
}

PHPLLVMAPI zval *ZVAL_FETCH_ARRAY_STRING_ELEMENT(zval *arrayZval, uint nKeyLength, char *arKey, uint forWrite) {
    zval *returnZval;
    if (!arrayZval || arrayZval->type != ZVAL_TYPE_ARRAY) {
        return NULL;
    }
    returnZval = (zval *) hash_find_string_index(arrayZval->hashtable, nKeyLength, arKey);
    if (forWrite && (!returnZval)) {
        zval *srcZval = ZVAL_INIT();
        hash_add_or_update_string_index(arrayZval->hashtable, srcZval, nKeyLength, arKey);
        return srcZval;
    }
    return returnZval;
}

PHPLLVMAPI zval *ZVAL_FETCH_ARRAY_ZVAL_ELEMENT(zval *arrayZval, zval *keyZval, uint forWrite) {
    ulong index = 0;
    uint nKeyLength = 0;
    char arKey[64];
    char *arKeyPtr = &arKey[0];

    if (!arrayZval || arrayZval->type != ZVAL_TYPE_ARRAY) {
        return NULL;
    }
    if (keyZval) {
        switch (keyZval->type) {
            case ZVAL_TYPE_BOOLEAN:
                index = keyZval->value.lval;
                break;
            case ZVAL_TYPE_INTEGER:
                if (keyZval->value.lval < 0) {
                    nKeyLength = snprintf(arKey, sizeof (arKey), "%ld", keyZval->value.lval);
                    break;
                }
                index = keyZval->value.lval;
                break;
            case ZVAL_TYPE_DOUBLE:
                if (keyZval->value.dval < 0) {
                    nKeyLength = snprintf(arKey, sizeof (arKey), "%ld", (long) keyZval->value.dval);
                    break;
                }
                index = (ulong) keyZval->value.dval;
                break;
            case ZVAL_TYPE_STRING:
                if (is_number(keyZval->value.str.len, keyZval->value.str.val)) {
                    long intValue = ZVAL_INTEGER_VALUE(keyZval);
                    if (intValue < 0) {
                        nKeyLength = snprintf(arKey, sizeof (arKey), "%ld", intValue);
                        break;
                    }
                    index = (ulong) intValue;
                    break;
                }
                arKeyPtr = keyZval->value.str.val;
                nKeyLength = keyZval->value.str.len;
                break;
            default:
                break;
        }
    }

    if (nKeyLength) {
        return (zval *) hash_find_string_index(arrayZval->hashtable, nKeyLength, arKeyPtr);
    }
    if (index) {
        return (zval *) hash_find_index(arrayZval->hashtable, index);
    }
    if (index == 0 && nKeyLength == 0) {
        return (zval *) hash_find_index(arrayZval->hashtable, index);
    }
    return NULL;
}

PHPLLVMAPI iterate *ZVAL_ITERATE_INIT(zval *arrayZval) {
    iterate *iterate_object;
    if ((!arrayZval) || (!arrayZval->hashtable)) {
        return NULL;
    }
    iterate_object = emalloc(sizeof (iterate));
    iterate_object->current = arrayZval->hashtable->pListHead;
    iterate_object->ht = arrayZval->hashtable;
    return iterate_object;
}

PHPLLVMAPI void ZVAL_ITERATE_FREE(iterate *iterate_object) {
    if (iterate_object) {
        efree(iterate_object);
    }
}

PHPLLVMAPI uint ZVAL_ITERATE_IS_END(iterate *iterate_object) {
    if (!iterate_object->current) {
        return TRUE;
    }
    return FALSE;
}

PHPLLVMAPI zval *ZVAL_ITERATE_CURRENT_KEY(iterate *iterate_object) {
    zval *keyZval;
    if (!iterate_object->current) {
        return NULL;
    }
    keyZval = ZVAL_INIT();
    if (!iterate_object->current->nKeyLength) {
        keyZval = ZVAL_ASSIGN_INTEGER(keyZval, iterate_object->current->h);
    } else {
        keyZval = ZVAL_ASSIGN_STRING(keyZval, iterate_object->current->nKeyLength, iterate_object->current->arKey);
    }
    return keyZval;
}

PHPLLVMAPI zval *ZVAL_ITERATE_CURRENT_VALUE(iterate *iterate_object) {
    if (!iterate_object->current) {
        return NULL;
    }
    return (zval *) iterate_object->current->pData;
}

PHPLLVMAPI void ZVAL_ITERATE_NEXT(iterate *iterate_object) {
    if (!iterate_object->current) {
        return;
    }
    iterate_object->current = iterate_object->current->pListNext;
}