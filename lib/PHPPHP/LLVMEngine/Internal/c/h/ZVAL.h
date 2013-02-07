#ifndef __ZVAL_H
#define __ZVAL_H
#include "common.h"
#include "zval_type.h"

void __attribute((fastcall)) freeConvertionCacheBuffer(zval *zval);
int __attribute((fastcall)) is_number(int len,char *val);

zval * __attribute((fastcall)) ZVAL_INIT(zvallist *list);
void __attribute((fastcall)) ZVAL_GC_REGISTER(zvallist *list, zval *zval);
void __attribute((fastcall)) ZVAL_GC(zvallist *list, zval *zval);

zval * __attribute((fastcall)) ZVAL_COPY(zvallist *list, zval *zval);
zval * __attribute((fastcall)) ZVAL_COPY_ON_WRITE(zvallist *list, zval *zval);
zval * __attribute((fastcall)) ZVAL_ASSIGN_BOOLEAN(zvallist *list, zval *zval, long val);
zval * __attribute((fastcall)) ZVAL_ASSIGN_INTEGER(zvallist *list, zval *zval, long val);
zval * __attribute((fastcall)) ZVAL_ASSIGN_DOUBLE(zvallist *list, zval *zval, double val);
zval * __attribute((fastcall)) ZVAL_ASSIGN_Zval(zvallist *list1, zval *zval1,zvallist *list2, zval *zval2);
zval * __attribute((fastcall)) ZVAL_ASSIGN_CONCAT_STRING(zvallist *list, zval *zval, int len, char *val);
zval * __attribute((fastcall)) ZVAL_ASSIGN_CONCAT_ZVAL(zvallist *list, zval *zval1, zval *zval2);
zval * __attribute((fastcall)) ZVAL_ASSIGN_REF(zvallist *list, zval *zval);
void __attribute((fastcall)) ZVAL_STRING_VALUE(zval *zval,int *len,char **str);
void __attribute((fastcall)) ZVAL_CONVERT_STRING(zval *zval);
long __attribute((fastcall)) ZVAL_INTEGER_VALUE(zval *zval);
void __attribute((fastcall)) ZVAL_CONVERT_INTEGER(zval *zval);
double __attribute((fastcall)) ZVAL_DOUBLE_VALUE(zval *zval);
void __attribute((fastcall)) ZVAL_CONVERT_DOUBLE(zval *zval);
int __attribute((fastcall)) ZVAL_TYPE_GUESS(zval *zval);
int __attribute((fastcall)) ZVAL_TYPE_GUESS_NUMBER(zval *zval);
int __attribute((fastcall)) ZVAL_TYPE_CAST_NUMBER(zval *zvalop1, zval *zvalop2, type_cast *value_op1, type_cast *value_op2);
int __attribute((fastcall)) ZVAL_TYPE_CAST_NUMBER_SINGLE(int type, zval *zvalop1, type_cast *value_op1);
long __attribute((fastcall)) ZVAL_EQUAL(zval *zvalop1, zval *zvalop2);
long __attribute((fastcall)) ZVAL_EQUAL_EXACT(zval *zvalop1, zval *zvalop2);

void __attribute((fastcall)) single_debug(int);

#endif