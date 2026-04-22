import paramiko

HOST = '4.233.149.242'
USER = 'voltgym'
PASS = 'voltgym@ubuntu24'

ssh_client = paramiko.SSHClient()
ssh_client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh_client.connect(hostname=HOST, username=USER, password=PASS)

stdin, stdout, stderr = ssh_client.exec_command("cd /home/voltgym/fitapp/voltgym-infra && docker compose logs laravel")
logs = stdout.read().decode()
if logs:
    print(logs[-2000:])
else:
    print(stderr.read().decode())

ssh_client.close()
