#include<stdio.h>
#include "h/functions.h"
#include "h/PHPLLVM_T_ECHO.h"
HashTable functionStore;

PHPLLVMAPI void PHPLLVM_FUNCTION_REGISTER(uint functionNameLen, char *functionName, void *functionPtr) {
    hash_add_or_update(&functionStore, functionName, functionNameLen, 0, functionPtr, NULL);
}


PHPLLVMAPI void PHPLLVM_FUNCTION_CALL_BY_NAME(fcall *fcall_obj) {
    if(fcall_obj->realfunction){
        return;
    }
    fcall_obj->realfunction = hash_find(&functionStore, fcall_obj->fname, fcall_obj->len, 0);
    if (!fcall_obj->realfunction) {
        printf("function %.*s not found!\n", fcall_obj->len, fcall_obj->fname);
        exit(-1);
    }
    getchar();
}

PHPLLVMAPI void single_debug(int i){
    printf("single debug:%d\n",i);
    getchar();
}