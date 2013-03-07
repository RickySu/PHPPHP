#include<stdio.h>
#include<stdlib.h>
#include "h/ZVAL_LIST.h"

extern int zvalcount;

PHPLLVMAPI void ZVAL_LIST_GC(HashTable *list) {
    hash_destroy(list);
    printf("zvalcount:%d\n", zvalcount);
    efree(list);
}

HashTable * ZVAL_LIST_INIT() {
    HashTable *list;
    list = emalloc(sizeof (HashTable));
    hash_init(list, ZVAL_LIST_SIZE, &zval_list_dtor);
    return list;
}

PHPLLVMAPI void ZVAL_GC_REGISTER(HashTable *list, zval **ZvalPtr, uint varNameLen, char *varName) {
    if (varNameLen) {
        hash_add_or_update(list, varName, varNameLen, 0, ZvalPtr, NULL);
        return;
    }
    hash_add_or_update(list, (char *) &ZvalPtr, sizeof (ZvalPtr), 0, ZvalPtr, NULL);
}

FASTCC void zval_list_dtor(void *pDest) {
    zval **ZvalPtr = (zval **) pDest;
    if(*ZvalPtr){
        ZVAL_GC(*ZvalPtr);
    }
}
