#ifndef __ZVAL_H
#define __ZVAL_H

#include "zval_type.h"

zval * __attribute((fastcall)) ZVAL_INIT(zvallist *list);
void __attribute((fastcall)) ZVAL_GC_REGISTER(zvallist *list,zval *zval);
void __attribute((fastcall)) ZVAL_GC(zval *zval);

void __attribute((fastcall)) ZVAL_ASSIGN_INTEGER(zval *zval,int val);
void __attribute((fastcall)) ZVAL_ASSIGN_DOUBLE(zval *zval,double val);
void __attribute((fastcall)) ZVAL_ASSIGN_STRING(zval *zval,int len,char *val);

#endif