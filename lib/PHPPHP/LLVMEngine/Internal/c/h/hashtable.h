/*
   +----------------------------------------------------------------------+
   | Zend Engine                                                          |
   +----------------------------------------------------------------------+
   | Copyright (c) 1998-2012 Zend Technologies Ltd. (http://www.zend.com) |
   +----------------------------------------------------------------------+
   | This source file is subject to version 2.00 of the Zend license,     |
   | that is bundled with this package in the file LICENSE, and is        |
   | available through the world-wide-web at the following url:           |
   | http://www.zend.com/license/2_00.txt.                                |
   | If you did not receive a copy of the Zend license and are unable to  |
   | obtain it through the world-wide-web, please send a note to          |
   | license@zend.com so we can mail you a copy immediately.              |
   +----------------------------------------------------------------------+
   | Authors: Andi Gutmans <andi@zend.com>                                |
   |          Zeev Suraski <zeev@zend.com>                                |
   +----------------------------------------------------------------------+
*/

#ifndef __HASHTABLE_H
#define __HASHTABLE_H

#include "common.h"

typedef void (*dtor_func_t)(void *pDest);

struct _hashtable;

typedef struct bucket {
        ulong h;
        uint nKeyLength;
        void *pData;
        void *pDataPtr;
        struct bucket *pListNext;
        struct bucket *pListLast;
        struct bucket *pNext;
        struct bucket *pLast;
        char arKey[1]; /* Must be last element */
} Bucket;

typedef struct _hashtable {
        uint nTableSize;
        uint nTableMask;
        uint nNumOfElements;
        ulong nNextFreeElement;
        Bucket *pInternalPointer;       /* Used for element traversal */
        Bucket *pListHead;
        Bucket *pListTail;
        Bucket **arBuckets;
        dtor_func_t pDestructor;
        BOOL persistent;
        unsigned char nApplyCount;
        BOOL bApplyProtection;
} HashTable;


int hash_init(HashTable *ht);
int hash_lookup(HashTable *ht, char *key, void **result);
int hash_insert(HashTable *ht, char *key, void *value);
int hash_remove(HashTable *ht, char *key);
int hash_destroy(HashTable *ht);

#endif