#include<stdio.h>
#include "PHPLLVM_T_ECHO.h"

void __attribute((fastcall)) PHPLLVM_T_ECHO(int length,char *string){
    printf("%.*s", length, string);
}

void __attribute((fastcall)) PHPLLVM_T_ECHO_ZVAL(zval *zval){
    switch(zval->type){
        case ZVAL_TYPE_INTEGER:
            printf("%ld",zval->value.lval);
            break;
        case ZVAL_TYPE_STRING:
            printf("%.*s",zval->value.str.len,zval->value.str.val);
            break;
        case ZVAL_TYPE_DOUBLE:
            printf("%.6g",zval->value.dval);
            break;
        case ZVAL_TYPE_BOOLEAN:
            break;
    }
}
