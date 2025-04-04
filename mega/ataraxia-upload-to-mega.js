// Import required modules
import express from 'express';
import { Storage } from 'megajs';
import bodyParser from 'body-parser';

// Initialize Express app
const app = express();
app.use(bodyParser.json());

// Node.js API endpoint for file upload
app.post('/upload', (req, res) => {
    const { filePath, fileName } = req.body;

    const storage = new Storage({
        email: 'psychopath.epy@gmail.com',
        password: '42230822579@taraxia',
        userAgent: 'Ataraxia/1.0'
    }).ready;

    storage.once('ready', () => {
        storage.upload(filePath, fileName, (error, file) => {
            if (error) {
                return res.status(500).send('There was an error: ' + error);
            }
            res.send('The file was uploaded!');
        });
    });

    storage.once('error', error => {
        res.status(500).send('Storage error: ' + error);
    });
});

// Start the server
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
