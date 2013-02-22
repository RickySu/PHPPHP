#include<stdio.h>
#include<stdlib.h>
#include "h/ZVAL_LIST.h"
#include "h/ZVAL.h"

PHPLLVMAPI void ZVAL_LIST_GC(zvallist *list) {
    if (list == NULL) {
        return;
    }
    for (int i = 0; i < list->count; i++) {
        ZVAL_GC(NULL, list->zval[i]);
    }
    ZVAL_LIST_GC(list->next);
    efree(list);
}

zvallist * ZVAL_LIST_INIT() {
    zvallist *list;
    list = emalloc(sizeof (zvallist));
    list->zval = emalloc(sizeof (zval) * ZVAL_LIST_SIZE);
    list->len = ZVAL_LIST_SIZE;
    list->count = 0;
    list->next = NULL;
    return list;
}
