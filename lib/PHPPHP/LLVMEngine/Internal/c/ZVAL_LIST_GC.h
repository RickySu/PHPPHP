#ifndef __ZVAL_LIST_GC_H
#define __ZVAL_LIST_GC_H
#include "zval.h"

typedef struct _zvallist zvallist;

struct _zvallist {
    zvallist *prev;
    zval *zval;
};

void __attribute((fastcall)) ZVAL_LIST_GC(zvallist *list);

#endif