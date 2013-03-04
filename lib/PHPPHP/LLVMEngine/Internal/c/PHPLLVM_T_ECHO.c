#include<stdio.h>
#include "h/PHPLLVM_T_ECHO.h"
#include "h/dtoa.h"
#include "h/hashtable.h"

void __attribute((fastcall)) printr_zval_array(zval *varZval, uint level) {
    int i;
    Bucket *p;
    if (varZval->type != ZVAL_TYPE_ARRAY) {
        PHPLLVM_T_ECHO_ZVAL(varZval);
        return;
    }
    printf("Array\n");
    for (i = 0; i < level * 4; i++) putchar(' ');
    printf("(\n");
    p = varZval->hashtable->pListHead;
    while (p) {
        for (i = 0; i < (level+1) * 4; i++) putchar(' ');
        if(p->nKeyLength){
            printf("[%.*s] => ",p->nKeyLength,p->arKey);
        }else{
            printf("[%ld] => ",p->h);
        }
        if(p->pData){
            printr_zval_array((zval *)p->pData, level+2);
            printf("\n");
        }
        p = p->pListNext;
    }
    for (i = 0; i < level * 4; i++) putchar(' ');
    printf(")\n");
}

void __attribute((fastcall)) PHPLLVM_T_PRINTR(zval *varZval) {
    if (!varZval) {
        return;
    }
    printr_zval_array(varZval, 0);
}

void __attribute((fastcall)) PHPLLVM_T_ECHO(int length, char *string) {
    printf("%.*s", length, string);
}

void __attribute((fastcall)) PHPLLVM_T_ECHO_ZVAL(zval *varZval) {
    char buffer[128];
    if (!varZval) {
        return;
    }
    switch (varZval->type) {
        case ZVAL_TYPE_BOOLEAN:
            if (varZval->value.lval) {
                printf("1");
            }
            break;
        case ZVAL_TYPE_INTEGER:
            printf("%ld", varZval->value.lval);
            break;
        case ZVAL_TYPE_STRING:
            printf("%.*s", varZval->value.str.len, varZval->value.str.val);
            break;
        case ZVAL_TYPE_DOUBLE:
            php_gcvt(varZval->value.dval, DTOA_DISPLAY_DIGITS, '.', 'e', buffer);
            printf("%s", buffer);
            break;
        case ZVAL_TYPE_ARRAY:
            PHPLLVM_T_PRINTR(varZval);
            break;
        case ZVAL_TYPE_NULL:
        default:
            break;
    }
}
