#ifndef __ZVAL_GC_H
#define __ZVAL_GC_H
#include "zval_type.h"

#define ZVAL_LIST_SIZE    1000

zvallist * ZVAL_LIST_INIT();
void __attribute((fastcall)) ZVAL_LIST_GC(zvallist *list);
void __attribute((fastcall)) ZVAL_GC(zval *zval);

#endif