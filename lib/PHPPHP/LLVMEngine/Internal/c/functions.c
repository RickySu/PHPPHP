#include<stdio.h>
#include "h/functions.h"
#include "h/PHPLLVM_T_ECHO.h"
HashTable functionStore;
void *callbackObj=NULL;
fcall_t LLVMBind_triggerCallback=NULL;

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
}

PHPLLVMAPI void single_debug(int i){
    printf("single debug:%d\n",i);
    getchar();
}

void LLVMBind_stTriggerCallbackEntryPointet(void *object, fcall_t call){
    if(callbackObj){
        return;
    }
    callbackObj=object;
    LLVMBind_triggerCallback=call;
    printf("set callback ok!\n");
}

PHPLLVMAPI void PHPLLVM_TRIGGER_CALLBACK(int callbackIndex, int len, char *message) {
    if(LLVMBind_triggerCallback){
        LLVMBind_triggerCallback(callbackObj, callbackIndex, len, message);
    }
}