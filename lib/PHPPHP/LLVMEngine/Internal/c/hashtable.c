#include<stdio.h>
#include "h/hashtable.h"

int hash_init(HashTable *ht, uint nSize, dtor_func_t pDestructor) {
    uint i = 3;
    if (nSize >= 0x80000000) {
        /* prevent overflow */
        ht->nTableSize = 0x80000000;
    } else {
        while ((1U << i) < nSize) {
            i++;
        }
        ht->nTableSize = 1 << i;
    }
    ht->nTableMask = ht->nTableSize - 1;
    ht->pDestructor = pDestructor;
    ht->arBuckets = NULL;
    ht->pListHead = NULL;
    ht->pListTail = NULL;
    ht->nNumOfElements = 0;
    ht->nNextFreeElement = 0;
    ht->pInternalPointer = NULL;
    ht->persistent = FALSE;
    ht->nApplyCount = 0;
    ht->bApplyProtection = 1;
    ht->arBuckets = (Bucket **) ecalloc(ht->nTableSize, sizeof (Bucket *));
    if (ht->arBuckets) {
        return SUCCESS;
    }
    return FAILED;
}

int hash_add_or_update(HashTable *ht, const char *arKey, uint nKeyLength, ulong h, void *pData, void **pDest) {
    uint nIndex;
    Bucket *p;

    if(nKeyLength){
        h = zend_inline_hash_func(arKey, nKeyLength);
    }

    nIndex = h & ht->nTableMask;
    p = ht->arBuckets[nIndex];
    while (p != NULL) {
        if ((p->h == h) && (p->nKeyLength == nKeyLength)) {
            if (!memcmp(p->arKey, arKey, nKeyLength)) {
                if (ht->pDestructor) {
                    ht->pDestructor(p->pData);
                }
                p->pData = pData;
                if (pDest) {
                    *pDest = p->pData;
                }
                return SUCCESS;
            }
        }
        p = p->pNext;
    }

    p = (Bucket *) emalloc(sizeof (Bucket) - 1 + nKeyLength);
    p->pNext=p->pListNext=NULL;
    if (!p) {
        return FAILED;
    }
    memcpy(p->arKey, arKey, nKeyLength);
    p->nKeyLength = nKeyLength;

    p->pData = pData;
    p->h = h;

    if (pDest) {
        *pDest = p->pData;
    }
    p->pLast=ht->arBuckets[nIndex];
    if(p->pLast){
        p->pLast->pNext=p;
    }
    ht->arBuckets[nIndex] = p;

    if(!ht->pListHead){
        ht->pListHead=p;
    }

    if(ht->pListTail){
        ht->pListTail->pListNext=p;
        p->pListLast=ht->pListTail;
    }

    ht->pListTail=p;
    printf("p->pListNext:%p\n",p->pListNext);
    printf("zval:%p Data:%p\n",pData,p->pData);
    ht->nNumOfElements++;
    return SUCCESS;
}

int hash_destroy(HashTable *ht) {
    Bucket *p, *q;
    p = ht->pListHead;
    while (p != NULL) {
        q = p;
        p = p->pListNext;
        if (ht->pDestructor) {
            printf("debug\n");
            ht->pDestructor(q->pData);
            printf("debug2\n");
        }
        efree(q);
    }
    printf("end\n");
    efree(ht->arBuckets);
    return SUCCESS;
}