#include<stdio.h>
#include<stdlib.h>
#include "h/ZVAL_LIST.h"
#include "h/ZVAL.h"

void __attribute((fastcall)) ZVAL_TEMP_LIST_GC_MIN(zvallist *list) {
    int i;
    if(list == NULL){
        return;
    }
    if(list->count==ZVAL_TEMP_LIST_SIZE){
        for(i=0;i<ZVAL_TEMP_LIST_SIZE/2;i++){
            ZVAL_GC(NULL, list->zval[i]);
            list->zval[i]=list->zval[i+ZVAL_TEMP_LIST_SIZE/2];
            list->count--;
        }
    }
}

void __attribute((fastcall)) ZVAL_TEMP_LIST_GC(zvallist *list) {
    printf("temp list:%d\n",list->count);
    ZVAL_LIST_GC(list);
}

void __attribute((fastcall)) ZVAL_LIST_GC(zvallist *list) {
    if (list == NULL) {
        return;
    }
    for (int i = 0; i < list->count; i++) {
        ZVAL_GC(NULL, list->zval[i]);
    }
    printf("list %p , %d\n", list, list->count);
    ZVAL_LIST_GC(list->next);
    free(list);
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

zvallist * ZVAL_TEMP_LIST_INIT() {
    zvallist *list;
    list = malloc(sizeof (zvallist));
    list->zval = malloc(sizeof (zval) * ZVAL_TEMP_LIST_SIZE);
    list->len = ZVAL_TEMP_LIST_SIZE;
    list->count = 0;
    list->next = NULL;
    return list;
}