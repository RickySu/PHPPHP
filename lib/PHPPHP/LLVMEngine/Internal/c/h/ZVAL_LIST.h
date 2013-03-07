#ifndef __ZVAL_LIST_H
#define __ZVAL_LIST_H
#include "common.h"
#include "zval_type.h"
#include "hashtable.h"
#include "ZVAL.h"

#define ZVAL_LIST_SIZE    1000

HashTable * ZVAL_LIST_INIT();
PHPLLVMAPI void ZVAL_LIST_GC(HashTable *list);
PHPLLVMAPI void ZVAL_GC_REGISTER(HashTable *list, zval **ZvalPtr, uint varNameLen, char *varName);
FASTCC void zval_list_dtor(void *pDest);

#endif
