#ifndef __ZVAL_TYPE_H
#define __ZVAL_TYPE_H

#define ZVAL_TYPE_NULL          0
#define ZVAL_TYPE_INTEGER       1
#define ZVAL_TYPE_STRING        2
#define ZVAL_TYPE_DOUBLE        3
#define ZVAL_TYPE_BOOLEAN       4
#define ZVAL_TYPE_ARRAY         5
#define ZVAL_TYPE_OBJECT        6
#define ZVAL_TYPE_RESOURCE      7


#include "common.h"
#include "hashtable.h"

typedef struct _zval_struct zval;
typedef struct _zvallist zvallist;

struct _zvallist {
    int  len;
    int  count;
    zval **zval;
    zvallist* next;
};

typedef union {
    long lval; /* long value */
    double dval; /* double value */
} type_cast;

typedef union {
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
        HashTable *hashtable;
        char _convertion_cache_type;
        zvalue_value _convertion_cache;
};

#endif