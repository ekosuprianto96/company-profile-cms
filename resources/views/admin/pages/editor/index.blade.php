<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>

    <link href="https://unpkg.com/grapesjs/dist/css/grapes.min.css" rel="stylesheet">
    <script src="https://unpkg.com/grapesjs"></script>
    <script src="https://unpkg.com/grapesjs-preset-webpage"></script>

    <style>
        .gjs-pn-panels {
            display: flex;
            justify-content: center;
        }

        .gjs-pn-devices-panel {
            
        }
    </style>
</head>
<body>

    <div id="gjs"></div>
    <div id="blocks"></div>
    
    <script>
        (function() {
            let editor = grapesjs.init({
                container: '#gjs',
                panels: {},
                plugins: [
                    (editor, otions) => {
                        editor.getConfig().showDevices = false;

                        const codeButton = editor.Panels.getButton("options", "export-template");
	                    codeButton.collection.remove(codeButton);
                    }
                ],
                deviceManager: {
                    devices: []
                },
                panels: {
                    // options
                }
            });

            const deviceManager = editor.Devices;
            const panelManager = editor.Panels;

            panelManager.addPanel({
                id: ''
            })

            panelManager.addPanel({
                id: 'devices-panel',
                visible: true,
                buttons: [
                    {
                        id: 'device-desktop',
                        className: 'fa fa-laptop',
                        attributes: { title: 'Desktop' },
                        command: {
                            run: () => deviceManager.select('desktop'),
                            stop: () => {}
                        },
                        active: true // Set default active
                    },
                    {
                        id: 'device-tablet',
                        className: 'fa fa-tablet',
                        attributes: { title: 'Tablet' },
                        command: {
                            run: () => deviceManager.select('tablet'),
                            stop: () => {}
                        },
                        active: false
                    },
                    {
                        id: 'device-mobile',
                        className: 'fa fa-mobile',
                        attributes: { title: 'Mobile' },
                        command: {
                            run: () => deviceManager.select('mobile'),
                            stop: () => {}
                        },
                        active: false
                    }
                ]
            });

            deviceManager.add({
                id: 'tablet',
                name: 'tablet',
                width: '768px'
            });

            deviceManager.add({
                id: 'mobile',
                name: 'mobile',
                width: '480px'
            });

            deviceManager.add({
                id: 'desktop',
                name: 'desktop',
                width: ''
            });

            deviceManager.select('desktop');
        })();
    </script>
</body>
</html>