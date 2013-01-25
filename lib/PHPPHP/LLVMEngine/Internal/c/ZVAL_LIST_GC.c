#include<stdio.h>
#include "ZVAL_LIST_GC.h"

void __attribute((fastcall)) ZVAL_LIST_GC(zvallist *list) {
    while (list != NULL) {
        list = list->prev;
    }
    printf("mytest\n");
}
