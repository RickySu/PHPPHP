#include<stdio.h>
#include "h/PHPLLVM_T_ECHO.h"
#include "h/dtoa.h"

void __attribute((fastcall)) PHPLLVM_T_ECHO(int length, char *string) {
    printf("%.*s", length, string);
}

void __attribute((fastcall)) PHPLLVM_T_ECHO_ZVAL(zval *zval) {
    char buffer[128];
    switch (zval->type) {
        case ZVAL_TYPE_BOOLEAN:
            if (zval->value.lval) {
                printf("1");
            }
            break;
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
