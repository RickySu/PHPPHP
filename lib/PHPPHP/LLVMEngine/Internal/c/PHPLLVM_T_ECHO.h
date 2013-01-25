#ifndef __PHPLLVM_T_ECHO_H
#define __PHPLLVM_T_ECHO_H
#include "zval.h"

void __attribute((fastcall)) PHPLLVM_T_ECHO(int length,char *string);
void __attribute((fastcall)) PHPLLVM_T_ECHO_ZVAL(zval *zval);

#endif