#ifndef __ZVAL_H
#define __ZVAL_H
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

#endif