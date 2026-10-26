// Local sink only: no messages are forwarded to the Internet.
const net = require('node:net');
const fs = require('node:fs');
let count = 0;
const server = net.createServer(socket => {
    let input = '', data = false, message = [];
    socket.setEncoding('utf8');
    socket.write('220 localhost test SMTP\r\n');
    socket.on('data', chunk => {
        input += chunk;
        while (input.includes('\n')) {
            const index = input.indexOf('\n');
            const line = input.slice(0, index).replace(/\r$/, '');
            input = input.slice(index + 1);
            if (data) {
                if (line === '.') {
                    fs.writeFileSync('storage/framework/testing/mail-captured-' + (++count) + '.eml', message.join('\r\n'));
                    message = []; data = false; socket.write('250 accepted\r\n');
                    console.log('Captured test message ' + count);
                } else message.push(line.startsWith('..') ? line.slice(1) : line);
            } else if (/^(EHLO|HELO)/i.test(line)) socket.write('250-localhost\r\n250 8BITMIME\r\n');
            else if (/^DATA/i.test(line)) { data = true; socket.write('354 end with dot\r\n'); }
            else if (/^QUIT/i.test(line)) socket.end('221 bye\r\n');
            else socket.write('250 OK\r\n');
        }
    });
    socket.on('error', () => {});
});
server.listen(2527, '127.0.0.1', () => console.log('Local SMTP capture listening on 127.0.0.1:2527'));
