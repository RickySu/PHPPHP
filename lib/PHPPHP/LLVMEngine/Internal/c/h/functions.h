#ifndef __FUNCTIONS_H
#define __FUNCTIONS_H

#include "ZVAL.h"
#include "hashtable.h"

extern HashTable functionStore;
typedef void (*fcall_t)();

typedef struct _fcall_struct fcall;

struct _fcall_struct {
    uint len;
    char * fname;
    zval * (*realfunction) (fcall *fcall_obj, int nArg, ...);
};

PHPLLVMAPI void PHPLLVM_FUNCTION_REGISTER(uint functionNameLen, char *functionName, void *functionPtr);
PHPLLVMAPI void PHPLLVM_FUNCTION_CALL_BY_NAME(fcall *fcall_obj);
void LLVMBind_stTriggerCallbackEntryPointet(void *object, fcall_t call);
PHPLLVMAPI void PHPLLVM_TRIGGER_CALLBACK(int callbackIndex, int len, char *message);
#endif