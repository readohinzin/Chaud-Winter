# Génère theme/chaud-winter/languages/chaud-winter.pot (textes à traduire, pour l'allemand plus tard).
# Usage : python3 outils/generer-pot.py theme/chaud-winter > theme/chaud-winter/languages/chaud-winter.pot
import re, sys, pathlib, datetime
root = pathlib.Path(sys.argv[1]); out = {}
pat = re.compile(r"\b(__|_e|esc_html__|esc_html_e|esc_attr__|esc_attr_e|_n)\(\s*'((?:[^'\\]|\\.)*)'\s*(?:,\s*'((?:[^'\\]|\\.)*)'\s*)?(?:,\s*\$\w+\s*)?,\s*'chaud-winter'\s*\)")
for f in sorted(root.rglob('*.php')):
    src = f.read_text()
    for m in pat.finditer(src):
        line = src[:m.start()].count('\n') + 1
        fn, a, b = m.groups()
        a = a.replace("\\'", "'")
        key = (a, b.replace("\\'", "'") if fn == '_n' else None)
        out.setdefault(key, []).append(f"{f.relative_to(root)}:{line}")
esc = lambda s: s.replace('\\', '\\\\').replace('"', '\\"')
print('# Chaud Winter : modèle de traduction.\nmsgid ""\nmsgstr ""\n"Project-Id-Version: Chaud Winter\\n"\n"MIME-Version: 1.0\\n"\n"Content-Type: text/plain; charset=UTF-8\\n"\n"Content-Transfer-Encoding: 8bit\\n"\n"X-Domain: chaud-winter\\n"\n')
for (a, b), refs in out.items():
    print('#: ' + ' '.join(refs))
    if '%' in a: print('#, php-format')
    print(f'msgid "{esc(a)}"')
    if b is not None: print(f'msgid_plural "{esc(b)}"\nmsgstr[0] ""\nmsgstr[1] ""\n')
    else: print('msgstr ""\n')
