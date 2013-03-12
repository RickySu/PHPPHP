#ifndef __GC_H
#define __GC_H
#include "common.h"
#include "zval_type.h"
#include "hashtable.h"
#include "ZVAL_LIST.h"
#include "ZVAL.h"

#define  COLOR_BLACK     0
#define  COLOR_WHITE     1
#define  COLOR_PURPLE    2
#define  COLOR_GRAY      3

#define  gc_is_mark_color(varZval,color)  (varZval->_gc_color == color)
typedef FASTCC void (*trace_dfs_cb_t)(zval *varZval);

void jit_init();
void jit_shutdown();
FASTCC void gc_pool_dtor(void *pDest);
FASTCC void gc_pool_add(zval *varZval);
FASTCC void gc_pool_remove(zval *varZval);
FASTCC void gc_zval_trace_dfs(zval *varZval,trace_dfs_cb_t trace_cb);

FASTCC void gc_trace_purple(zval *varZval);
FASTCC void gc_trace_gray(zval *varZval);
FASTCC void gc_trace_white(zval *varZval);

inline void gc_mark(zval *varZval,char color) {
    varZval->_gc_color = color;
}

#endif
