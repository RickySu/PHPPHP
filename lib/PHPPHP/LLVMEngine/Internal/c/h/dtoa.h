#ifndef __DTOA_H
#define __DTOA_H
#define IEEE_8087 1
#define DTOA_DISPLAY_DIGITS    15
char * g_fmt(register char *b, double x);
char *dtoa(double, int, int, int *, int *, char **);
void freedtoa(char *s);
char *php_gcvt(double value, int ndigit, char dec_point, char exponent, char *buf);
#endif
