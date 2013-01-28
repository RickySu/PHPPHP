#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include "ZVAL.h"
#include "ZVAL_LIST.h"

zval * __attribute((fastcall)) ZVAL_INIT(zvallist *list) {
    zval * zval;
    zval = malloc(sizeof (zval));
    ZVAL_GC_REGISTER(list,zval);
    return zval;
}

void __attribute((fastcall)) ZVAL_GC_REGISTER(zvallist *list, zval *zval) {
    if (list->count == list->len) {
        ZVAL_GC_REGISTER(list->next,zval);
        return;
    }
    list->zval[list->count++] = zval;
}

void __attribute((fastcall)) ZVAL_GC(zval *zval) {
    if (--zval->refcount) {
        return;
    }
    switch (zval->type) {
        case ZVAL_TYPE_STRING:
            free(zval->value.str.val);
            break;
        default:
            break;
    }
    free(zval);
}

void __attribute((fastcall)) ZVAL_ASSIGN_INTEGER(zval *zval, int val) {
    zval->refcount = 1;
    zval->type = ZVAL_TYPE_INTEGER;
    zval->value.lval = val;
}

void __attribute((fastcall)) ZVAL_ASSIGN_DOUBLE(zval *zval, double val) {
    zval->refcount = 1;
    zval->type = ZVAL_TYPE_DOUBLE;
    zval->value.dval = val;
}

void __attribute((fastcall)) ZVAL_ASSIGN_STRING(zval *zval, int len, char *val) {
    zval->refcount = 1;
    zval->type = ZVAL_TYPE_STRING;
    zval->value.str.val = malloc(len);
    zval->value.str.len = len;
    memcpy(zval->value.str.val, val, len);
}