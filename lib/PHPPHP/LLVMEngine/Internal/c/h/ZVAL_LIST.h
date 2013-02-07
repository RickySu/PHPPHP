#ifndef __ZVAL_LIST_H
#define __ZVAL_LIST_H
#include "common.h"
#include "zval_type.h"

#define ZVAL_LIST_SIZE    1000
#define ZVAL_TEMP_LIST_SIZE    20

zvallist * ZVAL_LIST_INIT();
void __attribute((fastcall)) ZVAL_LIST_GC(zvallist *list);

zvallist * ZVAL_TEMP_LIST_INIT();
void __attribute((fastcall)) ZVAL_TEMP_LIST_GC(zvallist *list);
void __attribute((fastcall)) ZVAL_TEMP_LIST_GC_MIN(zvallist *list);

#endif
