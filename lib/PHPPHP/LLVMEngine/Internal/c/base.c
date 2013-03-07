#include <stdio.h>
#include <stdlib.h>
#include "h/hashtable.h"
#include "h/ZVAL_LIST.h"
#include "h/ZVAL.h"
#include "h/base.h"

extern int zvalcount;

HashTable LLVMPHPGCPool;

void jit_init() {
    hash_init(&LLVMPHPGCPool, PHPLLVMGCPOOLSIZE, &phpllvm_gc_pool_dtor);
    printf("init\n");
}

FASTCC void phpllvm_gc_pool_dtor(void *pDest) {
    zval *varZval=(zval*)pDest;
    printf("gc pool gc:%p\n", pDest);
}

void phpllvm_gc_pool_add(zval *varZval) {
    if(LLVMPHPGCPool.nNumOfElements >= LLVMPHPGCPool.nTableSize){
        hash_destroy(&LLVMPHPGCPool);
        hash_init(&LLVMPHPGCPool, PHPLLVMGCPOOLSIZE, &phpllvm_gc_pool_dtor);
    }
    hash_add_or_update(&LLVMPHPGCPool, (const char *) &varZval, sizeof (varZval), 0, varZval, NULL);
}

void phpllvm_gc_pool_remove(zval *varZval) {
    hash_delete(&LLVMPHPGCPool, (const char *)varZval, sizeof (varZval), 0);
}


void jit_shutdown() {
    hash_destroy(&LLVMPHPGCPool);
    printf("zvalcount:%d\n", zvalcount);
    printf("shutdown\n");
}