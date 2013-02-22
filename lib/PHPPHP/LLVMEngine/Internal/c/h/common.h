#ifndef __COMMON_H
#define __COMMON_H
#include <stdlib.h>

#define emalloc(x) malloc(x)
#define ecalloc(x,y) calloc(x,y)
#define erealloc(x,y) realloc(x,y)

#define efree(x)   free(x)

#ifndef uint
#define uint       unsigned
#endif

#ifndef ulong
#define ulong      unsigned long
#endif

#ifndef BOOL
#define BOOL       char
#endif

#ifndef TRUE
#define TRUE       (1==1)
#endif

#ifndef SUCCESS
#define SUCCESS      FALSE
#endif


#ifndef FALSE
#define FALSE       (1==0)
#endif

#ifndef FAILED
#define FAILED      FALSE
#endif

#ifndef PHPLLVMAPI
#define PHPLLVMAPI  __attribute((fastcall))
#endif
#endif
