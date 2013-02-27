#include<stdio.h>
#include<stdlib.h>
#include "h/ZVAL_LIST.h"
#include "h/ZVAL.h"

PHPLLVMAPI void ZVAL_LIST_GC(zvallist *list) {
    if (list == NULL) {
        return;
    }
    for (int i = 0; i < list->count; i++) {
        if(*list->arZvalPtr[i]){
            ZVAL_GC(*list->arZvalPtr[i]);
        }
    }
    ZVAL_LIST_GC(list->next);
    efree(list);
}

zvallist * ZVAL_LIST_INIT() {
    zvallist *list;
    list = emalloc(sizeof (zvallist));
    list->arZvalPtr = emalloc(sizeof (zval**) * ZVAL_LIST_SIZE);
    list->len = ZVAL_LIST_SIZE;
    list->count = 0;
    list->next = NULL;
    return list;
}

PHPLLVMAPI void ZVAL_GC_REGISTER(zvallist *list, zval **ZvalPtr) {
    if (list->count == list->len) {
        list->next = ZVAL_LIST_INIT();
        ZVAL_GC_REGISTER(list->next, ZvalPtr);
        return;
    }
    list->arZvalPtr[list->count++] = ZvalPtr;
}

