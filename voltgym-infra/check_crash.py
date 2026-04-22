import paramiko
import os
import sys

HOST = '4.233.149.242'
USER = 'voltgym'
PASS = 'voltgym@ubuntu24'

ssh_client = paramiko.SSHClient()
ssh_client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
ssh_client.connect(hostname=HOST, username=USER, password=PASS)

def exec_print(cmd):
    print(f"Running: {cmd}")
    stdin, stdout, stderr = ssh_client.exec_command(cmd)
    for line in iter(stdout.readline, ""):
        print(line, end="")
    err = stderr.read().decode()
    if err:
        print("ERR:", err)

# Check docker status
exec_print("cd /home/voltgym/fitapp/voltgym-infra && docker ps")
print("=================== NGINX LOGS ====================")
exec_print("cd /home/voltgym/fitapp/voltgym-infra && docker compose logs nginx")
print("==================================================")
ssh_client.close()
