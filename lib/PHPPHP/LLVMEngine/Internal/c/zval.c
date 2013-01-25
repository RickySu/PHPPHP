#include<stdio.h>
#include<stdlib.h>
#include<string.h>
#include "zval.h"

zval * ZVAL_INIT() {
    return malloc(sizeof(zval));
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

void __attribute((fastcall)) ZVAL_ASSIGN_STRING(zval *zval,char *val,int len){
    zval->refcount=1;
    zval->type=ZVAL_TYPE_STRING;
    zval->value.str.val=malloc(len);
    zval->value.str.len=len;
    memcpy(zval->value.str.val,val,len);
}