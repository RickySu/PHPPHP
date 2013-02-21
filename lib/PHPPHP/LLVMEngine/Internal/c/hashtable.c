#include "h/hashtable.h"

int hash_init(HashTable *ht, uint nSize) {
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
    ht->pDestructor = NULL;
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

int hash_destroy(HashTable *ht){
    Bucket *p, *q;
    p = ht->pListHead;
    while (p != NULL) {
        q=p;
        p = p->pListNext;
        if (q->pData != &q->pDataPtr) {
            efree(q->pData);
        }
        efree(q);
    }
    efree(ht->arBuckets);
    return SUCCESS;
}