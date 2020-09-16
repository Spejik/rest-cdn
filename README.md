# rest-cdn
Rest-CDN is a project focused on creating a safe, very fast, and easy to setup & use CDN.

# Goals
- Short-term and Long-term storage
- Easy deployment
- Speed
- Security

### Short and Long-term storage
Rest-CDN can be used for both short-term storage and long-term storage
Files get by default deleted after 20 days from upload, but you can edit that limit in the file `/file.write.php`, or you can manually give some files more time in the file by adding some conditions, or manually editing the serialized *data index* object in `/data_storage/index.bin.php`

By adding some conditions I mean testing if the filename starts with `longterm.`, then we'd give it like a year before it would get deleted.

### Deployment
It's actually simple as fuk boiii, just upload all the files to a server that supports PHP (preferably Apache, I haven't tested it on Nginx or other servers, but it should theoretically work) and you're ✨done✨!
You should also change the access token in `/lib/tokens.php` since that is one of the biggest players in the security

### Speed
It's fast.

### Security
1. The main player in security of rest-cdn are Tokens
  - Tokens are used mainly to authenticate you
  - You, as a client, are required to send a token with every request, so the server is sure that it can save, change, read or delete a file
2. Data Storage Token
  - Data Storage Tokens are a way for the server to make sure that anyone who doesn't have access to the server where rest-cdn is placed can access your files
  - The way this works is simple: you send a request, the server changes something
    - The Something is the Data Storage Token, but don't get this confused with User Access Tokens (point 1.)
    - The server renames the directory inside `/data_storage` to a sequence of 32 cryptographically secure random bytes (or 64 characters in hex)
3. Apache access control (in a future update) 
  - I will put some .htaccess files into the project in a future update to increase the security even more.
  - Going to `/lib/`, `/data_storage/` or some other forbidden file will simply give you 403
