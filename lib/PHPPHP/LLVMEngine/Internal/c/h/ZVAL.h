#ifndef __ZVAL_H
#define __ZVAL_H
#include "common.h"
#include "zval_type.h"

PHPLLVMAPI void freeConvertionCacheBuffer(zval *zval);
PHPLLVMAPI int is_number(int len, char *val);
PHPLLVMAPI void zval_copy_content(zval *dstZval,zval *srcZval);
PHPLLVMAPI void emptyZval(zval *varZval);
void hashtable_zval_gc_dtor(void *pDest);

PHPLLVMAPI zval *  ZVAL_INIT(zvallist *list);
PHPLLVMAPI void ZVAL_INIT_ARRAY(zval *aZval);
PHPLLVMAPI void ZVAL_GC_REGISTER(zvallist *list, zval *zval);
PHPLLVMAPI void ZVAL_GC(zvallist *list, zval *zval);

PHPLLVMAPI zval *  ZVAL_COPY(zvallist *list, zval *zval);
PHPLLVMAPI zval *  ZVAL_COPY_ON_WRITE(zvallist *list, zval *zval);
PHPLLVMAPI void ZVAL_ASSIGN_ARRAY_NEXT_ELEMENT(zval *dstZval, zval *srcZval);
PHPLLVMAPI zval *  ZVAL_ASSIGN_BOOLEAN(zvallist *list, zval *zval, long val);
PHPLLVMAPI zval *  ZVAL_ASSIGN_INTEGER(zvallist *list, zval *zval, long val);
PHPLLVMAPI zval *  ZVAL_ASSIGN_DOUBLE(zvallist *list, zval *zval, double val);
PHPLLVMAPI zval *  ZVAL_ASSIGN_Zval(zvallist *list1, zval *zval1, zvallist *list2, zval *zval2);
PHPLLVMAPI zval *  ZVAL_ASSIGN_CONCAT_STRING(zvallist *list, zval *zval, int len, char *val);
PHPLLVMAPI zval *  ZVAL_ASSIGN_CONCAT_ZVAL(zvallist *list, zval *zval1, zval *zval2);
PHPLLVMAPI zval *  ZVAL_ASSIGN_REF(zvallist *list, zval *zval);
PHPLLVMAPI void ZVAL_STRING_VALUE(zval *zval, int *len, char **str);
PHPLLVMAPI void ZVAL_CONVERT_STRING(zval *zval);
PHPLLVMAPI long ZVAL_INTEGER_VALUE(zval *zval);
PHPLLVMAPI void ZVAL_CONVERT_INTEGER(zval *zval);
PHPLLVMAPI double ZVAL_DOUBLE_VALUE(zval *zval);
PHPLLVMAPI void ZVAL_CONVERT_DOUBLE(zval *zval);
PHPLLVMAPI int ZVAL_TYPE_GUESS(zval *zval);
PHPLLVMAPI int ZVAL_TYPE_GUESS_NUMBER(zval *zval);
PHPLLVMAPI int ZVAL_TYPE_CAST_NUMBER(zval *zvalop1, zval *zvalop2, type_cast *value_op1, type_cast *value_op2);
PHPLLVMAPI int ZVAL_TYPE_CAST_NUMBER_SINGLE(zval *zvalop1, type_cast *value_op1);
PHPLLVMAPI int ZVAL_TYPE_CAST_SINGLE(zval *zvalop1, type_cast *value_op1);
PHPLLVMAPI long ZVAL_EQUAL_STRING(zval *zvalop1, int len, char *val);
PHPLLVMAPI long ZVAL_EQUAL(zval *zvalop1, zval *zvalop2);
PHPLLVMAPI long ZVAL_EQUAL_EXACT(zval *zvalop1, zval *zvalop2);
PHPLLVMAPI long ZVAL_TEST_NULL(zval *zvalop1);
PHPLLVMAPI long ZVAL_TEST_FALSE(zval *zvalop1);
#endif