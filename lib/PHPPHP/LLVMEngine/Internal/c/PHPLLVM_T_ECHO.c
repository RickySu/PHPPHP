#include<stdio.h>
#include "PHPLLVM_T_ECHO.h"
#include "dtoa.h"

void __attribute((fastcall)) PHPLLVM_T_ECHO(int length, char *string) {
    printf("%.*s", length, string);
}

void __attribute((fastcall)) PHPLLVM_T_ECHO_ZVAL(zval *zval) {
    char buffer[128];
    printf("is_ref:%d refcount:%d\n",zval->is_ref,zval->refcount);
    switch (zval->type) {
        case ZVAL_TYPE_BOOLEAN:
        case ZVAL_TYPE_INTEGER:
            printf("%ld", zval->value.lval);
            break;
        case ZVAL_TYPE_STRING:
            printf("%.*s", zval->value.str.len, zval->value.str.val);
            break;
        case ZVAL_TYPE_DOUBLE:
            php_gcvt(zval->value.dval, DTOA_DISPLAY_DIGITS, '.', 'e', buffer);
            printf("%s", buffer);
            break;
        case ZVAL_TYPE_NULL:
        default:
            break;
    }
}
