#include <stdio.h>
#include <stdlib.h>
#include "h/gc.h"
#include "h/functions.h"

extern int zvalcount;

HashTable LLVMPHPGCPool;

void jit_init() {
    hash_init(&LLVMPHPGCPool, PHPLLVMGCPOOLSIZE, &gc_pool_dtor);
    hash_init(&functionStore, PHP_FUNCTION_STORE_SIZE, NULL);
    printf("init\n");
}

FASTCC void gc_pool_dtor(void *pDest) {
    //see http://www.php.net/manual/en/features.gc.collecting-cycles.php
    gc_zval_trace_dfs((zval*) pDest, &gc_trace_purple);
    gc_zval_trace_dfs((zval*) pDest, &gc_trace_gray);
    gc_zval_trace_dfs((zval*) pDest, &gc_trace_white);
}

FASTCC void gc_pool_add(zval *varZval) {
    if(!gc_is_mark_color(varZval, COLOR_BLACK)){
        return;
    }
    if (LLVMPHPGCPool.nNumOfElements >= LLVMPHPGCPool.nTableSize) {
        hash_destroy(&LLVMPHPGCPool);
        hash_init(&LLVMPHPGCPool, PHPLLVMGCPOOLSIZE, &gc_pool_dtor);
    }
    gc_mark(varZval, COLOR_PURPLE);
    hash_add_or_update(&LLVMPHPGCPool, (const char *) &varZval, sizeof (varZval), 0, varZval, NULL);
}

FASTCC void gc_pool_remove(zval *varZval) {
    hash_delete(&LLVMPHPGCPool, (const char *) varZval, sizeof (varZval), 0);
}

void jit_shutdown() {
    hash_destroy(&LLVMPHPGCPool);
    hash_destroy(&functionStore);
    printf("zvalcount:%d\n", zvalcount);
    printf("shutdown\n");
}

FASTCC void gc_trace_purple(zval *varZval) {
    if ((!varZval->hashtable) || (!gc_is_mark_color(varZval, COLOR_PURPLE))) {
        return;
    }
    varZval->refcount--;
    gc_mark(varZval, COLOR_GRAY);
    gc_zval_trace_dfs(varZval, &gc_trace_purple);
}

FASTCC void gc_trace_gray(zval *varZval) {
    if ((!varZval->hashtable) || (!gc_is_mark_color(varZval, COLOR_GRAY))) {
        return;
    }
    if(varZval->refcount==0){
        gc_mark(varZval, COLOR_WHITE);
    }
    else{
        gc_mark(varZval, COLOR_BLACK);
        varZval->refcount++;
    }
    gc_zval_trace_dfs(varZval, &gc_trace_gray);
}

FASTCC void gc_trace_white(zval *varZval) {
    if ((!varZval->hashtable) || (!gc_is_mark_color(varZval, COLOR_WHITE))) {
        return;
    }
    gc_mark(varZval, COLOR_BLACK);
    gc_zval_trace_dfs(varZval, &gc_trace_white);
    zval_gc_real(varZval);
}


FASTCC void gc_zval_trace_dfs(zval *varZval, trace_dfs_cb_t trace_cb) {
    Bucket *p;
    if (!varZval->hashtable) {
        return;
    }
    p = varZval->hashtable->pListHead;
    while (p) {
        if (trace_cb && varZval) {
            trace_cb((zval *) p->pData);
        }
        p = p->pListNext;
    }
}