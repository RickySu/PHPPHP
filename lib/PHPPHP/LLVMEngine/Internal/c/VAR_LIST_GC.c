#include<stdio.h>
#include "VAR_LIST_GC.h"

void __attribute((fastcall)) VAR_LIST_GC(varlist *list) {
    while (list != NULL) {
        list = list->prev;
    }
    printf("mytest\n");
}
