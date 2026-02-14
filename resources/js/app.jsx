// import React from "react";
// import { createRoot } from "react-dom/client";
// import BeamGridBackground from "./components/BeamGridBackground";

// console.log("VITE APP LOADED");

// const el = document.getElementById("beam-bg");

// if (el) {
//     createRoot(el).render(
//         <BeamGridBackground
//             asBackground
//             interactive
//             gridSize={24}
//             gridColor="rgba(255,255,255,0.12)"
//             darkGridColor="rgba(255,255,255,0.08)"
//             beamColor="rgba(0,180,255,0.7)"
//             darkBeamColor="rgba(0,255,255,0.7)"
//             beamCount={4}
//             extraBeamCount={2}
//             beamThickness={2}
//             glowIntensity={35}
//             className="w-full h-full min-h-screen opacity-80"
//         />
//     );
// }

import './bootstrap'; 
import Alpine from 'alpinejs';
import React from "react";
import { createRoot } from "react-dom/client";
import AnimatedWave from "./components/AnimatedWave";

// Initialize Alpine
window.Alpine = Alpine;
Alpine.start();

console.log("VITE APP LOADED");

const el = document.getElementById("beam-bg");

if (el) {
    createRoot(el).render(
        <AnimatedWave
            waveColor="#8b5cf6"
            className="w-full h-full"
            speed={0.015}
            amplitude={25}
            smoothness={320}
            wireframe={true}
            opacity={0.25}
            quality="medium"
            mouseInteraction={true}
            waveOffsetY={-250}
            waveRotation={30}
            cameraDistance={-1000}
        />
    );
}