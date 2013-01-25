#include<stdio.h>
#include<stdlib.h>
#include "zval.h"

zval * ZVAL_INIT() {
    return malloc(sizeof(zval));
}
