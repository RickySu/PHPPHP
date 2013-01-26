#include<stdio.h>
#include<stdlib.h>
#include "ZVAL_GC.h"

void __attribute((fastcall)) ZVAL_LIST_GC(zvallist *list) {
    if (list == NULL) {
        return;
    }
    for (int i = 0; i < list->count; i++) {
        ZVAL_GC(list->zval[i]);
    }
    ZVAL_LIST_GC(list->next);
    free(list);
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

zvallist * ZVAL_LIST_INIT() {
    zvallist *list;
    list = malloc(sizeof (zvallist));
    list->zval = malloc(sizeof (zval) * ZVAL_LIST_SIZE);
    list->len = ZVAL_LIST_SIZE;
    list->count = 0;
    list->next = NULL;
    return list;
}