#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include "zval.h"
#include "ZVAL_GC.h"

zval * __attribute((fastcall)) ZVAL_INIT(zvallist *list) {
    zval * zval;
    if(list->count == list->len){
        return ZVAL_INIT(list->next);
    }
    zval=malloc(sizeof(zval));
    list->zval[list->count++]=zval;
    if(list->count == list->len){
        list->next=ZVAL_LIST_INIT();
    }
    return zval;
}

void __attribute((fastcall)) ZVAL_ASSIGN_INTEGER(zval *zval,int val){
    zval->refcount=1;
    zval->type=ZVAL_TYPE_INTEGER;
    zval->value.lval=val;
}

void __attribute((fastcall)) ZVAL_ASSIGN_DOUBLE(zval *zval,double val){
    zval->refcount=1;
    zval->type=ZVAL_TYPE_DOUBLE;
    zval->value.dval=val;
}

void __attribute((fastcall)) ZVAL_ASSIGN_STRING(zval *zval,int len,char *val){
    zval->refcount=1;
    zval->type=ZVAL_TYPE_STRING;
    zval->value.str.val=malloc(len);
    zval->value.str.len=len;
    memcpy(zval->value.str.val,val,len);
}