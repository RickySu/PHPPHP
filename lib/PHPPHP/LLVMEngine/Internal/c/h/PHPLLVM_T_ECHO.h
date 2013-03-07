#ifndef __PHPLLVM_T_ECHO_H
#define __PHPLLVM_T_ECHO_H
#include "common.h"
#include "ZVAL.h"

void FASTCC PHPLLVM_T_ECHO(int length,char *string);
void FASTCC PHPLLVM_T_ECHO_ZVAL(zval *zval);
void FASTCC PHPLLVM_T_PRINTR(zval *varZval);
void FASTCC printr_zval_array(zval *varZval,uint level);
#endif