#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include "ZVAL.h"
#include "ZVAL_LIST.h"
#include "dtoa.h"

zval * __attribute((fastcall)) ZVAL_INIT(zvallist *list) {
    zval * aZval;
    aZval = malloc(sizeof (zval));
    memset(aZval, 0, sizeof (zval));
    ZVAL_GC_REGISTER(list, aZval);
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
            free(zval->value.str.val);
            break;
        default:
            break;
    }
    free(zval);
}

zval * __attribute((fastcall)) ZVAL_COPY_ON_WRITE(zvallist *list, zval *oldzval) {
    zval *newzval;
    newzval = ZVAL_INIT(list);
    newzval = malloc(sizeof (zval));
    memcpy(newzval, oldzval, sizeof (zval));
    newzval->is_ref = 0;
    newzval->refcount = 1;
    if (newzval->type == ZVAL_TYPE_STRING) {
        newzval->value.str.val = malloc(oldzval->value.str.len);
        newzval->value.str.len = oldzval->value.str.len;
        memcpy(newzval->value.str.val, oldzval->value.str.val, oldzval->value.str.len);
    }
    ZVAL_GC(list, oldzval);
    return newzval;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_INTEGER(zvallist *list, zval *zval, int val) {
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
    if (zval->value.str.val) {
        free(zval->value.str.val);
    }
    zval->value.str.val = malloc(len);
    zval->value.str.len = len;
    memcpy(zval->value.str.val, val, len);
    return zval;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_CONCAT_STRING(zvallist *list, zval *zval, int len, char *val) {
    int newlen;
    char *newval;
    newlen = zval->value.str.len + len;
    newval = malloc(newlen);
    memcpy(newval, zval->value.str.val, zval->value.str.len);
    memcpy(&newval[zval->value.str.len], val, len);
    if (!zval->is_ref && zval->refcount > 1)
        zval = ZVAL_COPY_ON_WRITE(list, zval);
    zval->refcount = 1;
    zval->type = ZVAL_TYPE_STRING;
    if (zval->value.str.val) {
        free(zval->value.str.val);
    }
    zval->value.str.val = newval;
    zval->value.str.len = newlen;
    return zval;
}

zval * __attribute((fastcall)) ZVAL_ASSIGN_CONCAT_ZVAL(zvallist *list, zval *zval1, zval *zval2) {
    char tmpString[128];
    int newlen;
    char *newval;
    switch (zval2->type) {
        case ZVAL_TYPE_BOOLEAN:
        case ZVAL_TYPE_INTEGER:
            sprintf(tmpString, "%ld", zval2->value.lval);
            newlen = strlen(tmpString);
            newval = malloc(newlen + zval1->value.str.len);
            memcpy(newval, zval1->value.str.val, zval1->value.str.len);
            memcpy(&newval[zval1->value.str.len], tmpString, newlen);
            newlen += zval1->value.str.len;
            break;
        case ZVAL_TYPE_DOUBLE:
            php_gcvt(zval2->value.dval, DTOA_DISPLAY_DIGITS, '.', 'e', tmpString);
            newlen = strlen(tmpString);
            newval = malloc(newlen + zval1->value.str.len);
            memcpy(newval, zval1->value.str.val, zval1->value.str.len);
            memcpy(&newval[zval1->value.str.len], tmpString, newlen);
            newlen += zval1->value.str.len;
            break;
        case ZVAL_TYPE_STRING:
            newlen = zval1->value.str.len + zval2->value.str.len;
            newval = malloc(newlen);
            memcpy(newval, zval1->value.str.val, zval1->value.str.len);
            memcpy(&newval[zval1->value.str.len], zval2->value.str.val, zval2->value.str.len);
            break;
        case ZVAL_TYPE_NULL:
        default:
            break;
    }
    if (zval1->refcount > 1)
        zval1 = ZVAL_COPY_ON_WRITE(list, zval1);
    zval1->refcount = 1;
    zval1->type = ZVAL_TYPE_STRING;
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