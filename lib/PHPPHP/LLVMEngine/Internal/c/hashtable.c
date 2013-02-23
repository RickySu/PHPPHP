#include<stdio.h>
#include "h/hashtable.h"

#define UPDATE_DATA(ht, p, pData, nDataSize)											\
	if (nDataSize == sizeof(void*)) {													\
		if ((p)->pData != &(p)->pDataPtr) {												\
			efree((p)->pData);									\
		}																				\
		memcpy(&(p)->pDataPtr, pData, sizeof(void *));									\
		(p)->pData = &(p)->pDataPtr;													\
	} else {																			\
		if ((p)->pData == &(p)->pDataPtr) {												\
			(p)->pData = (void *) emalloc(nDataSize);			\
			(p)->pDataPtr=NULL;															\
		} else {																		\
			(p)->pData = (void *) realloc((p)->pData, nDataSize);	\
			/* (p)->pDataPtr is already NULL so no need to initialize it */				\
		}																				\
		memcpy((p)->pData, pData, nDataSize);											\
	}

#define INIT_DATA(ht, p, pData, nDataSize);								\
	if (nDataSize == sizeof(void*)) {									\
		p->pDataPtr = pData;					\
		(p)->pData = &(p)->pDataPtr;									\
	} else {															\
		(p)->pData = (void *) emalloc(nDataSize);\
		if (!(p)->pData) {												\
			efree(p);							\
			return FAILED;												\
		}																\
		memcpy((p)->pData, pData, nDataSize);							\
		(p)->pDataPtr=NULL;												\
	}

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

int hash_add_or_update(HashTable *ht, const char *arKey, uint nKeyLength, ulong h, void *pData, uint nDataSize, void **pDest) {
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
                UPDATE_DATA(ht, p, pData, nDataSize);
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
    INIT_DATA(ht, p, pData, nDataSize);
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
    printf("zval:%p Data:%p DataPtr:%p\n",pData,p->pData,p->pDataPtr);
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
            ht->pDestructor(q->pData);
        }
        if (q->pData != &q->pDataPtr) {
            efree(q->pData);
        }
        efree(q);
    }
    efree(ht->arBuckets);
    return SUCCESS;
}