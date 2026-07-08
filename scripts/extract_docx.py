#!/usr/bin/env python3
"""
Ekstrak teks + gambar dari file .docx soal.

- Teks linear per paragraf, dengan penanda [IMG:qXX.png] inline pada posisi
  gambar di dokumen.
- Gambar disalin ke storage/app/public/questions/{slug}/ sehingga bisa
  direferensikan sebagai /storage/questions/{slug}/{nama}.

Pemakaian:
    python3 scripts/extract_docx.py "<file.docx>" <slug> <project_root>

Output:
    storage/app/question-import/txt/{slug}.txt
    storage/app/public/questions/{slug}/imgN.{ext}
"""

import re
import sys
import zipfile
from pathlib import Path
from xml.etree import ElementTree as ET

NS = {
    'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
    'a': 'http://schemas.openxmlformats.org/drawingml/2006/main',
    'r': 'http://schemas.openxmlformats.org/officeDocument/2006/relationships',
    'rel': 'http://schemas.openxmlformats.org/package/2006/relationships',
    'v': 'urn:schemas-microsoft-com:vml',
}


def rel_map(zf: zipfile.ZipFile) -> dict:
    """rId -> target media path (word/media/...)"""
    mapping = {}
    try:
        root = ET.fromstring(zf.read('word/_rels/document.xml.rels'))
    except KeyError:
        return mapping
    for rel in root.findall('rel:Relationship', NS):
        target = rel.get('Target', '')
        if 'media/' in target:
            mapping[rel.get('Id')] = 'word/' + target.lstrip('/')
    return mapping


def paragraph_text(p) -> str:
    parts = []
    for node in p.iter():
        tag = node.tag.split('}')[-1]
        if tag == 't' and node.text:
            parts.append(node.text)
        elif tag in ('br', 'cr'):
            parts.append('\n')
        elif tag == 'tab':
            parts.append(' ')
    return ''.join(parts)


def paragraph_image_rids(p) -> list:
    """Semua rId gambar (drawing blip / VML imagedata) dalam paragraf."""
    rids = []
    for node in p.iter():
        tag = node.tag.split('}')[-1]
        if tag == 'blip':
            rid = node.get('{%s}embed' % NS['r'])
            if rid:
                rids.append(rid)
        elif tag == 'imagedata':
            rid = node.get('{%s}id' % NS['r'])
            if rid:
                rids.append(rid)
    return rids


def main():
    docx_path, slug, project_root = sys.argv[1], sys.argv[2], Path(sys.argv[3])

    txt_dir = project_root / 'storage/app/question-import/txt'
    img_dir = project_root / 'storage/app/public/questions' / slug
    txt_dir.mkdir(parents=True, exist_ok=True)

    zf = zipfile.ZipFile(docx_path)
    rels = rel_map(zf)
    doc = ET.fromstring(zf.read('word/document.xml'))
    body = doc.find('w:body', NS)

    saved = {}  # media path -> nama file publik
    counter = 0
    lines = []

    # Iterasi paragraf top-level DAN dalam tabel (urutan dokumen)
    for p in body.iter('{%s}p' % NS['w']):
        text = paragraph_text(p).strip()
        markers = []
        for rid in paragraph_image_rids(p):
            media = rels.get(rid)
            if not media:
                continue
            if media not in saved:
                counter += 1
                ext = Path(media).suffix or '.png'
                name = f'img{counter}{ext}'
                img_dir.mkdir(parents=True, exist_ok=True)
                (img_dir / name).write_bytes(zf.read(media))
                saved[media] = name
            markers.append(f'[IMG:{saved[media]}]')

        line = ' '.join(markers + ([text] if text else []))
        lines.append(line)

    # Rapikan: gabung, buang baris kosong beruntun > 1
    raw = '\n'.join(lines)
    raw = re.sub(r'\n{3,}', '\n\n', raw)

    out = txt_dir / f'{slug}.txt'
    out.write_text(raw.strip() + '\n', encoding='utf-8')

    print(f'{slug}: {len([l for l in raw.splitlines() if l.strip()])} baris teks, {counter} gambar')


if __name__ == '__main__':
    main()
