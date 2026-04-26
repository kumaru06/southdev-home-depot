import ftplib, socket, sys, os, ssl

FTP_HOST = 'ftpupload.net'
FTP_USER = 'if0_41705046'
FTP_PASS = 'markperez201'

class FTP_TLS_FixedPassive(ftplib.FTP_TLS):
    """FTPS client that handles servers returning 229 in response to PASV."""
    def makepasv(self):
        try:
            resp = self.sendcmd('PASV')
            host, port = ftplib.parse227(resp)
            return host, port
        except ftplib.error_reply as e:
            resp = str(e)
            if resp.startswith('229'):
                import re
                m = re.search(r'\|(\d+)\|', resp)
                if m:
                    port = int(m.group(1))
                    host = self.sock.getpeername()[0]
                    return host, port
            raise

files = [
    ('assets/css/customer.css',                     '/htdocs/assets/css/customer.css'),
    ('includes/footer.php',                         '/htdocs/includes/footer.php'),
    ('views/customer/featured-collections.php',     '/htdocs/views/customer/featured-collections.php'),
    ('views/customer/products.php',                 '/htdocs/views/customer/products.php'),
    ('views/customer/profile.php',                  '/htdocs/views/customer/profile.php'),
]

print('Connecting to', FTP_HOST)
ctx = ssl.create_default_context()
ctx.check_hostname = False
ctx.verify_mode = ssl.CERT_NONE
ftp = FTP_TLS_FixedPassive(context=ctx)
ftp.connect(FTP_HOST, 21, timeout=30)
print(ftp.getwelcome().splitlines()[0])
ftp.auth()
ftp.login(FTP_USER, FTP_PASS)
ftp.prot_p()  # protect data channel
print('Logged in successfully\n')
ftp.set_pasv(True)

ok = 0
failed = []
for local, remote in files:
    parts = remote.rsplit('/', 1)
    try:
        ftp.cwd('/')
        ftp.cwd(parts[0].lstrip('/'))
        print(f'Uploading {local} ...', end=' ', flush=True)
        with open(local, 'rb') as f:
            ftp.storbinary('STOR ' + parts[1], f)
        print('OK')
        ok += 1
    except Exception as ex:
        print(f'FAILED: {ex}')
        failed.append(remote)

ftp.quit()
print(f'\nUploaded: {ok}/{len(files)}')
if failed:
    print('Failed:', '\n  '.join(failed))
    sys.exit(1)
else:
    print('All files deployed successfully!')
