#ifndef __ZVAL_H
#define __ZVAL_H

#include "zval_type.h"

zval * __attribute((fastcall)) ZVAL_INIT(zvallist *list);
void __attribute((fastcall)) ZVAL_GC_REGISTER(zvallist *list, zval *zval);
void __attribute((fastcall)) ZVAL_GC(zvallist *list, zval *zval);

zval * __attribute((fastcall)) ZVAL_COPY_ON_WRITE(zvallist *list, zval *zval);
zval * __attribute((fastcall)) ZVAL_ASSIGN_INTEGER(zvallist *list, zval *zval, int val);
zval * __attribute((fastcall)) ZVAL_ASSIGN_DOUBLE(zvallist *list, zval *zval, double val);
zval * __attribute((fastcall)) ZVAL_ASSIGN_STRING(zvallist *list, zval *zval, int len, char *val);

#endif