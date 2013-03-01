#include<stdio.h>
#include "h/PHPLLVM_T_ECHO.h"
#include "h/dtoa.h"
#include "h/hashtable.h"

void __attribute((fastcall)) PHPLLVM_T_ECHO(int length, char *string) {
    printf("%.*s", length, string);
}

void __attribute((fastcall)) PHPLLVM_T_ECHO_ZVAL(zval *varZval) {
    char buffer[128];
    if(!varZval){
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
            printf("array\n");
            printf("ln:%d\n",varZval->hashtable->nNumOfElements);
            printf("list:%p %p\n",varZval->hashtable->pListHead,varZval->hashtable->pListTail);

            Bucket *p;
            p=varZval->hashtable->pListHead;
            while(p){
              printf("list:%p h:%lu",p,p->h);
              if(p->nKeyLength){
                  printf(" key:%.*s",p->nKeyLength,p->arKey);
              }
              printf("\n");
              //printf("zval:%p type:%d\n",p->pData,((zval*)p->pData)->type);
              PHPLLVM_T_ECHO_ZVAL((zval*)p->pData);
              printf("\n");
              p=p->pListNext;
            };

            for(int i=0;i<varZval->hashtable->nTableSize;i++){
                int nIndex;
                if(!varZval->hashtable->arBuckets[i]){
                    continue;
                }
                printf("Index:%d\n",i);
                p=varZval->hashtable->arBuckets[i];
                while(p){
                    printf("\tzval:");
                    PHPLLVM_T_ECHO_ZVAL((zval*)p->pData);
                    printf("\n");
                    p=p->pNext;
                }
            }

            break;
        case ZVAL_TYPE_NULL:
        default:
            break;
    }
}
