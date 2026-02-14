/*Ensure you had installed the package
or read our installation document. (go to lightswind.com/components/Installation)
npm i lightswind@latest*/

// "use client";

import React, { useEffect, useRef, useCallback, useState } from "react";
import * as THREE from "three";
import { createNoise2D } from "simplex-noise";
// import { cn } from '../lib/utils'; // Assuming this utility is correctly set up
const cn = (...classes) => classes.filter(Boolean).join(" ");

export interface AnimatedWaveProps {
    /** Custom CSS class name */
    className?: string;
    /** Wave animation speed (default: 0.015) */
    speed?: number;
    /** Wave amplitude/scale (default: 30) */
    amplitude?: number;
    /** Wave smoothness factor (default: 300) */
    smoothness?: number;
    /** Enable wireframe mode (default: true) */
    wireframe?: boolean;
    /** Wave color (CSS color string) */
    waveColor?: string;
    /** Wave opacity (0-1, default: 1) */
    opacity?: number;
    /** Enable mouse interaction (default: true) */
    mouseInteraction?: boolean;
    /** Render quality - higher = more detail but slower (default: 'medium') */
    quality?: "low" | "medium" | "high";
    /** Camera field of view (default: 60) */
    fov?: number;
    /** Wave position Y offset (default: -300) */
    waveOffsetY?: number;
    /** Wave rotation in degrees (default: 29.8) */
    waveRotation?: number;
    /** Camera position Z offset (default: -1000) */
    cameraDistance?: number;
    /** Auto-detect background color from parent to determine contrasting wave color */
    autoDetectBackground?: boolean;
    /** Background color for manual override (for the container div) */
    backgroundColor?: string;
    /** Wave ease factor (default: 12) */
    ease?: number;
    /** Mouse influence on wave distortion (default: 0.5) */
    mouseDistortionStrength?: number;
    /** How smooth the mouse distortion is (default: 100) */
    mouseDistortionSmoothness?: number;
    /** Time factor for mouse distortion decay (default: 0.0005) */
    mouseDistortionDecay?: number;
    /** Strength of the shrinking/scaling effect (default: 0.7) */
    mouseShrinkScaleStrength?: number;
    /** Radius of the shrinking/scaling effect (default: 200) */
    mouseShrinkScaleRadius?: number;
}

// --- Helper Functions ---

interface DeviceInfo {
    screenWidth: () => number;
    screenHeight: () => number;
    screenRatio: () => number;
    screenCenterX: () => number;
    screenCenterY: () => number;
    mouseCenterX: (e: MouseEvent) => number;
    mouseCenterY: (e: MouseEvent) => number;
}

const getDeviceInfo = (): DeviceInfo => {
    return {
        screenWidth: () =>
            Math.max(
                0,
                window.innerWidth ||
                    document.documentElement.clientWidth ||
                    document.body.clientWidth ||
                    0
            ),
        screenHeight: () =>
            Math.max(
                0,
                window.innerHeight ||
                    document.documentElement.clientHeight ||
                    document.body.clientHeight ||
                    0
            ),
        screenRatio: function () {
            return this.screenWidth() / this.screenHeight();
        },
        screenCenterX: function () {
            return this.screenWidth() / 2;
        },
        screenCenterY: function () {
            return this.screenHeight() / 2;
        },
        mouseCenterX: function (e: MouseEvent) {
            // Return mouse X position relative to the center of the screen
            // This maps 0,0 to the center, negative left, positive right
            return e.clientX - this.screenCenterX();
        },
        mouseCenterY: function (e: MouseEvent) {
            // Return mouse Y position relative to the center of the screen
            // This maps 0,0 to the center, negative up, positive down
            return e.clientY - this.screenCenterY();
        },
    };
};

const addEase = (
    pos: THREE.Vector3,
    to: { x: number; y: number; z: number },
    ease: number
) => {
    pos.x += (to.x - pos.x) / ease;
    pos.y += (to.y - pos.y) / ease;
    pos.z += (to.z - pos.z) / ease;
};

const getElementBackground = (element: HTMLElement): string | null => {
    let currentElement: HTMLElement | null = element;

    while (currentElement) {
        const style = getComputedStyle(currentElement);
        const bgColor = style.backgroundColor;

        if (
            bgColor &&
            bgColor !== "rgba(0, 0, 0, 0)" &&
            bgColor !== "transparent"
        ) {
            return bgColor;
        }

        currentElement = currentElement.parentElement;
    }

    return null;
};

const parseColor = (color: string): THREE.Color => {
    try {
        return new THREE.Color(color);
    } catch (error) {
        if (color.startsWith("rgb")) {
            const matches = color.match(/\d+/g);
            if (matches && matches.length >= 3) {
                return new THREE.Color(
                    parseInt(matches[0]) / 255,
                    parseInt(matches[1]) / 255,
                    parseInt(matches[2]) / 255
                );
            }
        }
        console.warn(`Could not parse color: ${color}. Falling back to white.`);
        return new THREE.Color(0xffffff); // Default fallback
    }
};

/**
 * Determines if a color is "dark" based on its luminance.
 * A common threshold for perceived brightness is used.
 * @param color A Three.js Color object.
 * @returns True if the color is dark, false if it's light.
 */
const isColorDark = (color: THREE.Color): boolean => {
    // Calculate luminance (perceived brightness)
    // Formula: L = 0.299*R + 0.587*G + 0.114*B
    const luminance = 0.299 * color.r + 0.587 * color.g + 0.114 * color.b;
    // A threshold of 0.5 is common, lower values mean 'darker'
    return luminance < 0.5;
};

// --- Main Component ---

const AnimatedWave: React.FC<AnimatedWaveProps> = ({
    className,
    speed = 0.015,
    amplitude = 30,
    smoothness = 300,
    wireframe = true,
    waveColor,
    opacity = 1,
    mouseInteraction = true,
    quality = "medium",
    fov = 60,
    waveOffsetY = -300,
    waveRotation = 29.8,
    cameraDistance = -1000,
    autoDetectBackground = true,
    backgroundColor,
    ease = 12,
    mouseDistortionStrength = 0.5,
    mouseDistortionSmoothness = 100,
    mouseDistortionDecay = 0.0005,
    mouseShrinkScaleStrength = 0.7,
    mouseShrinkScaleRadius = 200,
}) => {
    const containerRef = useRef<HTMLDivElement>(null);
    const sceneElementsRef = useRef<{
        scene: THREE.Scene | null;
        camera: THREE.PerspectiveCamera | null;
        renderer: THREE.WebGLRenderer | null;
        groundPlain: any | null; // Using 'any' for the custom groundPlain object with methods
        animationFrameId: number | null;
        mouse: { x: number; y: number };
    }>({
        scene: null,
        camera: null,
        renderer: null,
        groundPlain: null,
        animationFrameId: null,
        mouse: { x: 0, y: 0 },
    });

    const [webGLFailed, setWebGLFailed] = useState(false);

    // Determine the number of segments for the plane geometry based on quality prop
    const getQualitySettings = useCallback((quality: string) => {
        switch (quality) {
            case "low":
                return { width: 64, height: 32 }; // Fewer vertices, faster
            case "high":
                return { width: 256, height: 128 }; // More vertices, higher detail, slower
            default: // medium
                return { width: 128, height: 64 }; // Balanced
        }
    }, []);

    /**
     * Determines the wave color.
     * If waveColor is provided, it's used directly.
     * If autoDetectBackground is true, it attempts to find a contrasting color based on parent background.
     * Otherwise, it defaults to a sensible fallback (black for light, white for dark).
     */
    const determineWaveColor = useCallback((): THREE.Color => {
        if (waveColor) {
            return parseColor(waveColor); // Explicit color always wins
        }

        if (autoDetectBackground && containerRef.current) {
            const detectedBg = getElementBackground(containerRef.current);
            if (detectedBg) {
                const parsedBgColor = parseColor(detectedBg);
                if (isColorDark(parsedBgColor)) {
                    return new THREE.Color(0xffffff); // White wave for dark background
                } else {
                    // return new THREE.Color(0x000000); // Black wave for light background
                    return new THREE.Color("#6a00ff"); // Custom purple wave for light background, adds a nice contrast and fits well with the example gradient background
                }
            }
        }

        // Default based on assumed common scenario (light background -> dark wave)
        // Or you could make this a prop for more control: `defaultWaveColor`
        // For a typical website, default background is light, so default wave should be dark.
        // If you expect dark mode by default, change this to 0xffffff.
        // Here, we'll default to black assuming a light container, and let auto-detect handle dark mode.
        // return new THREE.Color(0x000000); // Default to black wave
        return new THREE.Color("#6a00ff"); // Default to a vibrant purple wave, which stands out against both light and dark backgrounds and adds a unique aesthetic touch.
    }, [waveColor, autoDetectBackground]);

    // Function to create and manage the ground plain (the wave)
    const createGroundPlain = useCallback(() => {
        const { width: geometryWidth, height: geometryHeight } =
            getQualitySettings(quality);

        const groundPlain = {
            group: null as THREE.Object3D | null,
            geometry: null as THREE.PlaneGeometry | null,
            material: null as THREE.MeshLambertMaterial | null,
            plane: null as THREE.Mesh | null,
            simplex: null as ReturnType<typeof createNoise2D> | null, // Simplex noise generator
            factor: smoothness, // Controls the "wavelength" of the noise
            scale: amplitude, // Controls the "height" of the noise
            speed: speed, // Controls how fast the wave moves over time
            cycle: 0, // Time counter for wave animation
            ease: ease, // Easing factor for plane movement
            // Initial position of the entire wave group in 3D space
            move: new THREE.Vector3(0, waveOffsetY, cameraDistance),
            // Initial rotation of the entire wave group
            look: new THREE.Vector3((waveRotation * Math.PI) / 180, 0, 0), // Convert degrees to radians for X-axis rotation

            // Mouse distortion properties
            mouseDistortionStrength: mouseDistortionStrength,
            mouseDistortionSmoothness: mouseDistortionSmoothness,
            mouseDistortionDecay: mouseDistortionDecay,
            distortionTime: 0, // Time counter for mouse ripple decay

            // Mouse shrink/scale properties
            mouseShrinkScaleStrength: mouseShrinkScaleStrength,
            mouseShrinkScaleRadius: mouseShrinkScaleRadius,

            // Store original vertex positions to apply transformations from a constant base
            _originalPositions: new Float32Array(),

            create: function (scene: THREE.Scene) {
                // Create a new Three.js Group to hold the plane, allowing easier positioning/rotation
                this.group = new THREE.Object3D();
                this.group.position.copy(this.move);
                this.group.rotation.copy(this.look);

                // Define the plane geometry (width, height, segmentsX, segmentsY)
                this.geometry = new THREE.PlaneGeometry(
                    4000, // Width of the plane in Three.js units
                    2000, // Height of the plane in Three.js units
                    geometryWidth, // Number of horizontal segments (vertices)
                    geometryHeight // Number of vertical segments (vertices)
                );

                // CRUCIAL: Store the initial (undistorted) vertex positions
                // This array will be used as the base for all calculations in moveNoise.
                this._originalPositions = new Float32Array(
                    this.geometry.attributes.position.array
                );

                // Set up the material for the plane
                const waveColorValue = determineWaveColor();
                this.material = new THREE.MeshLambertMaterial({
                    color: waveColorValue,
                    opacity: opacity,
                    blending:
                        opacity < 1 ? THREE.NormalBlending : THREE.NoBlending, // Correct blending for transparency
                    side: THREE.DoubleSide, // Render both front and back faces (important for wireframe)
                    transparent: opacity < 1, // Enable transparency if opacity < 1
                    depthWrite: opacity < 1 ? false : true, // Disable depth writes for transparent objects to prevent artifacts
                    wireframe: wireframe, // Show as wireframe or solid mesh
                });

                // Create the mesh (geometry + material)
                this.plane = new THREE.Mesh(this.geometry, this.material);
                this.plane.position.set(0, 0, 0); // Position the plane at the center of its group

                // Initialize Simplex noise generator
                this.simplex = createNoise2D();

                // Perform initial noise calculation (no mouse influence initially)
                this.moveNoise({ x: 0, y: 0 });

                this.group.add(this.plane); // Add the plane to the group
                scene.add(this.group); // Add the group to the scene
            },

            // Function to calculate and apply noise (wave + mouse effects) to vertices
            moveNoise: function (mouse: { x: number; y: number }) {
                if (!this.geometry || !this.simplex || !this._originalPositions)
                    return;

                const positions = this.geometry.attributes.position; // Get the position attribute of the geometry
                const currentMouseX = mouseInteraction ? mouse.x : 0;
                const currentMouseY = mouseInteraction ? mouse.y : 0;

                // Increment the time factor for mouse distortion decay
                this.distortionTime += this.mouseDistortionDecay;

                // Loop through all vertices
                for (let i = 0; i < positions.count; i++) {
                    // Retrieve original (undistorted) X and Y coordinates for the current vertex
                    const originalX = this._originalPositions[i * 3];
                    const originalY = this._originalPositions[i * 3 + 1];

                    // Initialize newX, newY, and zOffset with values based on original positions
                    let newX = originalX;
                    let newY = originalY;

                    // --- 1. Base Wave Effect (Z-axis displacement) ---
                    // Use originalX and originalY to calculate noise.
                    // `this.factor` (smoothness) controls the "wavelength".
                    // `this.cycle` provides the time-based animation.
                    const xoff_wave = originalX / this.factor;
                    const yoff_wave = originalY / this.factor + this.cycle;
                    let zOffset =
                        this.simplex(xoff_wave, yoff_wave) * this.scale; // `this.scale` (amplitude) controls height.

                    // --- 2. Mouse Distortion / Wobble Effect (Additional Z-axis displacement) ---
                    if (mouseInteraction && this.mouseDistortionStrength > 0) {
                        // Calculate distance of the original vertex from the mouse position.
                        // The `* 0.5` on currentMouseX/Y helps in centering the effect if needed.
                        const distX_mouse = originalX - currentMouseX * 0.5;
                        const distY_mouse = originalY - currentMouseY * 0.5;
                        const dist_mouse = Math.sqrt(
                            distX_mouse * distX_mouse +
                                distY_mouse * distY_mouse
                        );

                        // Generate a 3D Simplex noise value for the ripple.
                        // `this.distortionTime` makes the ripple evolve over time.
                        const mouseRippleNoise =
                            this.simplex(
                                distX_mouse / this.mouseDistortionSmoothness, // Smoothness of the mouse ripple
                                distY_mouse / this.mouseDistortionSmoothness,
                                this.distortionTime // Third dimension for time-based evolution
                            ) * this.mouseDistortionStrength; // Overall strength of the mouse ripple

                        // Apply a falloff (diminishing effect) as the vertex gets further from the mouse.
                        // The effect diminishes further from the mouse. Factor of 2 on radius for wider spread.
                        const zFalloff = Math.max(
                            0,
                            1 - dist_mouse / (this.mouseShrinkScaleRadius * 2)
                        );

                        // Add the mouse-induced ripple to the base Z offset, scaled by falloff
                        zOffset += mouseRippleNoise * this.scale * zFalloff;
                    }

                    // --- 3. Mouse Shrink/Scale Effect (X & Y axis displacement) ---
                    // This creates the "grid lines converging/expanding" visual.
                    if (mouseInteraction && this.mouseShrinkScaleStrength > 0) {
                        // Calculate distance of the original vertex from the exact mouse position
                        const distX_shrink = originalX - currentMouseX;
                        const distY_shrink = originalY - currentMouseY;
                        const dist_shrink = Math.sqrt(
                            distX_shrink * distX_shrink +
                                distY_shrink * distY_shrink
                        );

                        let shrinkFalloff = 0;
                        // Only apply effect if within the defined radius
                        if (dist_shrink < this.mouseShrinkScaleRadius) {
                            // Calculate a normalized falloff: 1 at mouse center, 0 at radius edge
                            shrinkFalloff =
                                1 - dist_shrink / this.mouseShrinkScaleRadius;
                            // Apply a power curve for a smoother ease-out effect (strongest near mouse, fades out gracefully)
                            shrinkFalloff = Math.pow(shrinkFalloff, 2);
                        }

                        // Calculate the total amount to move the vertex towards the mouse.
                        // Positive shrinkAmount pulls towards the mouse, creating a "shrink".
                        const shrinkAmount =
                            this.mouseShrinkScaleStrength * shrinkFalloff;

                        // Update newX and newY based on the original positions,
                        // moving them towards the mouse by the calculated shrinkAmount.
                        newX = originalX - distX_shrink * shrinkAmount;
                        newY = originalY - distY_shrink * shrinkAmount;
                    }

                    // Update the vertex position in the geometry's attribute buffer
                    positions.setXYZ(i, newX, newY, zOffset);
                }

                // Mark the positions attribute as needing an update for Three.js to re-render it
                positions.needsUpdate = true;
                this.cycle += this.speed; // Advance the wave animation cycle
            },

            update: function (mouse: { x: number; y: number }) {
                this.moveNoise(mouse);

                if (mouseInteraction && this.group) {
                    // Fix mouse direction: invert X to match natural movement and correct Y direction
                    this.move.x = -(mouse.x * 0.04);
                    this.move.y = waveOffsetY + mouse.y * 0.04; // Add Y movement with corrected direction
                    addEase(this.group.position, this.move, this.ease);
                    addEase(this.group.rotation, this.look, this.ease);
                }
            },

            // Clean up Three.js resources when the component unmounts or scene is re-setup
            dispose: function () {
                this.geometry?.dispose();
                this.material?.dispose();
                this.group?.remove(this.plane!);
                this.plane = null;
                this.geometry = null;
                this.material = null;
                this.simplex = null;
                this.group = null;
                this._originalPositions = new Float32Array(); // Clear the reference
            },
        };
        return groundPlain;
    }, [
        // Dependencies for useCallback, ensuring function is re-created if these change
        quality,
        smoothness,
        amplitude,
        speed,
        ease,
        waveOffsetY,
        cameraDistance,
        waveRotation,
        determineWaveColor, // Use the new function
        opacity,
        wireframe,
        mouseInteraction,
        getQualitySettings,
        mouseDistortionStrength,
        mouseDistortionSmoothness,
        mouseDistortionDecay,
        mouseShrinkScaleStrength,
        mouseShrinkScaleRadius,
    ]);

    // /////////////////////////////
    useEffect(() => {
    if (!containerRef.current) return;

    const scene = new THREE.Scene();

    const camera = new THREE.PerspectiveCamera(
        fov,
        containerRef.current.clientWidth / containerRef.current.clientHeight,
        1,
        5000
    ) as THREE.PerspectiveCamera;

    const renderer = new THREE.WebGLRenderer({ alpha: true });
    renderer.setSize(
        containerRef.current.clientWidth,
        containerRef.current.clientHeight
    );

    containerRef.current.appendChild(renderer.domElement);

    const ground = createGroundPlain();
    ground.create(scene);

    camera.position.z = 300;

    const animate = () => {
        ground.update({ x: 0, y: 0 });
        renderer.render(scene, camera);
        requestAnimationFrame(animate);
    };

    animate();

    return () => {
        ground.dispose();
        renderer.dispose();
        containerRef.current?.removeChild(renderer.domElement);
    };
    }, []);

    // /////////////////////////////

    return (
    <div
        ref={containerRef}
        style={{
            width: "100vw",
            height: "100vh",
        }}
        className={cn("relative", className)}
    />
)
};
export default AnimatedWave;

