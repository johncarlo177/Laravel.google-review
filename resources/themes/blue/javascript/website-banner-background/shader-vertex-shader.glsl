attribute vec4 aVertexPosition;

uniform mat4 uModelViewMatrix;
uniform mat4 uProjectionMatrix;

varying highp vec2 iuv;

void main() {
    gl_Position = uProjectionMatrix * uModelViewMatrix * aVertexPosition;
    iuv = aVertexPosition.xy * 0.5 + 0.5;
}