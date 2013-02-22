#ifndef __ZVAL_LIST_H
#define __ZVAL_LIST_H
#include "common.h"
#include "zval_type.h"

#define ZVAL_LIST_SIZE    1000

zvallist * ZVAL_LIST_INIT();
PHPLLVMAPI void ZVAL_LIST_GC(zvallist *list);
PHPLLVMAPI void ZVAL_LIST_ADD(zvallist *list);

#endif
