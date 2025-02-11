// Logger
const {onRequest} = require("firebase-functions/v2/https");
const logger = require("firebase-functions/logger");

// Express server 
const functions = require("firebase-functions");
const express = require("express");
const cors = require("cors");
const { exec } = require("child_process");

const app = express();
app.use(cors());
app.use(express.json());

app.post("/search", (req, res) => {
    const searchQuery = req.body.search_string;
    
    if (!searchQuery) {
        return res.status(400).json({ error: "Search string is required" });
    }

    // Log the search term (Optional)
    console.log(`Search query: ${searchQuery}`);

    // Execute Python script (replace with actual path)
    exec(`python3 related_queries.py "${searchQuery}"`, (error, stdout, stderr) => {
        if (error) {
            return res.status(500).json({ error: stderr || error.message });
        }
        res.json({ related_query: stdout.trim() });
    });
});

exports.api = functions.https.onRequest(app);
