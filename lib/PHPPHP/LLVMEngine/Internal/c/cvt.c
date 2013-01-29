#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include "dtoa.h"

static char * __cvt(double value, int ndigit, int *decpt, int *sign, int fmode, int pad) /* {{{ */
{
	register char *s = NULL;
	char *p, *rve, c;
	size_t siz;

	if (ndigit < 0) {
		siz = -ndigit + 1;
	} else {
		siz = ndigit + 1;
	}

	/* __dtoa() doesn't allocate space for 0 so we do it by hand */
	if (value == 0.0) {
		*decpt = 1 - fmode; /* 1 for 'e', 0 for 'f' */
		*sign = 0;
		if ((rve = s = (char *)malloc(ndigit?siz:2)) == NULL) {
			return(NULL);
		}
		*rve++ = '0';
		*rve = '\0';
		if (!ndigit) {
			return(s);
		}
	} else {
		p = dtoa(value, fmode + 2, ndigit, decpt, sign, &rve);
		if (*decpt == 9999) {
			/* Infinity or Nan, convert to inf or nan like printf */
			*decpt = 0;
			c = *p;
			freedtoa(p);
			return strdup((c == 'I' ? "INF" : "NAN"));
		}
		/* Make a local copy and adjust rve to be in terms of s */
		if (pad && fmode) {
			siz += *decpt;
		}
		if ((s = (char *)malloc(siz+1)) == NULL) {
			freedtoa(p);
			return(NULL);
		}
		(void) strncpy(s, p, siz);
		rve = s + (rve - p);
		freedtoa(p);
	}

	/* Add trailing zeros */
	if (pad) {
		siz -= rve - s;
		while (--siz) {
			*rve++ = '0';
		}
		*rve = '\0';
	}

	return(s);
}
/* }}} */

static inline char *php_ecvt(double value, int ndigit, int *decpt, int *sign) /* {{{ */
{
	return(__cvt(value, ndigit, decpt, sign, 0, 1));
}
/* }}} */

static inline char *php_fcvt(double value, int ndigit, int *decpt, int *sign) /* {{{ */
{
    return(__cvt(value, ndigit, decpt, sign, 1, 1));
}
/* }}} */

char *php_gcvt(double value, int ndigit, char dec_point, char exponent, char *buf) /* {{{ */
{
	char *digits, *dst, *src;
	int i, decpt, sign;

	digits = dtoa(value, 2, ndigit, &decpt, &sign, NULL);
	if (decpt == 9999) {
		/*
		 * Infinity or NaN, convert to inf or nan with sign.
		 * We assume the buffer is at least ndigit long.
		 */
		snprintf(buf, ndigit + 1, "%s%s", (sign && *digits == 'I') ? "-" : "", *digits == 'I' ? "INF" : "NAN");
		freedtoa(digits);
		return (buf);
	}

	dst = buf;
	if (sign) {
		*dst++ = '-';
	}

	if ((decpt >= 0 && decpt > ndigit) || decpt < -3) { /* use E-style */
		/* exponential format (e.g. 1.2345e+13) */
		if (--decpt < 0) {
			sign = 1;
			decpt = -decpt;
		} else {
			sign = 0;
		}
		src = digits;
		*dst++ = *src++;
		*dst++ = dec_point;
		if (*src == '\0') {
			*dst++ = '0';
		} else {
			do {
				*dst++ = *src++;
			} while (*src != '\0');
		}
		*dst++ = exponent;
		if (sign) {
			*dst++ = '-';
		} else {
			*dst++ = '+';
		}
		if (decpt < 10) {
			*dst++ = '0' + decpt;
			*dst = '\0';
		} else {
			/* XXX - optimize */
			for (sign = decpt, i = 0; (sign /= 10) != 0; i++)
				continue;
			dst[i + 1] = '\0';
			while (decpt != 0) {
				dst[i--] = '0' + decpt % 10;
				decpt /= 10;
			}
		}
	} else if (decpt < 0) {
		/* standard format 0. */
		*dst++ = '0';   /* zero before decimal point */
		*dst++ = dec_point;
		do {
			*dst++ = '0';
		} while (++decpt < 0);
		src = digits;
		while (*src != '\0') {
			*dst++ = *src++;
		}
		*dst = '\0';
	} else {
		/* standard format */
		for (i = 0, src = digits; i < decpt; i++) {
			if (*src != '\0') {
				*dst++ = *src++;
			} else {
				*dst++ = '0';
			}
		}
		if (*src != '\0') {
			if (src == digits) {
				*dst++ = '0';   /* zero before decimal point */
			}
			*dst++ = dec_point;
			for (i = decpt; digits[i] != '\0'; i++) {
                *dst++ = digits[i];
            }
        }
        *dst = '\0';
    }
    freedtoa(digits);
    return (buf);
}
/* }}} */
