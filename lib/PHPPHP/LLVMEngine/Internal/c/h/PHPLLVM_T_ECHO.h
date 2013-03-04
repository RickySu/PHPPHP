#ifndef __PHPLLVM_T_ECHO_H
#define __PHPLLVM_T_ECHO_H
#include "common.h"
#include "ZVAL.h"

void __attribute((fastcall)) PHPLLVM_T_ECHO(int length,char *string);
void __attribute((fastcall)) PHPLLVM_T_ECHO_ZVAL(zval *zval);
void __attribute((fastcall)) PHPLLVM_T_PRINTR(zval *varZval);
void __attribute((fastcall)) printr_zval_array(zval *varZval,uint level);
#endif