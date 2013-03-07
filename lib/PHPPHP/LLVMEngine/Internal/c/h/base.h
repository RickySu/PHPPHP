#ifndef __BASE_H
#define __BASE_H
#include "common.h"
#include "zval_type.h"
#define  PHPLLVMGCPOOLSIZE 100
void jit_init();
void jit_shutdown();
FASTCC void phpllvm_gc_pool_dtor(void *pDest);
void phpllvm_gc_pool_add(zval *varZval);
void phpllvm_gc_pool_remove(zval *varZval);

#endif
