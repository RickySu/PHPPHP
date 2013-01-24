#include<stdio.h>

void __attribute((fastcall)) PHPLLVM_T_ECHO(int length,char *string){
    printf("%.*s", length, string);
}
