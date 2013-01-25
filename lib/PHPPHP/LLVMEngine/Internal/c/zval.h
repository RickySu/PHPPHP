#ifndef __ZVAL_H
#define __ZVAL_H
#define ZVAL_TYPE_NULL  0
#define ZVAL_TYPE_INTEGER  1
#define ZVAL_TYPE_STRING  2
#define ZVAL_TYPE_DOUBLE  3
#define ZVAL_TYPE_BOOLEAN  4

typedef struct _zval_struct zval;

typedef union _zvalue_value {
    long lval; /* long value */
    double dval; /* double value */

    struct {
        char *val;
        int len;
    } str;
} zvalue_value;

struct _zval_struct {
        zvalue_value value;             /* value */
        int  refcount;
        char type;                     /* active type */
        char is_ref;
        struct {
            zval *prev;
            zval *next;
        } internal_link;
};

zval * ZVAL_INIT();
void __attribute((fastcall)) ZVAL_ASSIGN_INTEGER(zval *zval,int val);
void __attribute((fastcall)) ZVAL_ASSIGN_DOUBLE(zval *zval,double val);

#endif