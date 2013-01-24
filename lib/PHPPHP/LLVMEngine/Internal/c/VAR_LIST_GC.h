#ifndef __VAR_LIST_GC_H
#define __VAR_LIST_GC_H
#include "zval.h"

typedef struct _varlist varlist;

struct _varlist {
    varlist *prev;
    zval *zval;
};

void __attribute((fastcall)) VAR_LIST_GC(varlist *list);

#endif