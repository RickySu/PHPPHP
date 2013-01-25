#include<stdio.h>
#include<stdlib.h>
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
