varying highp vec2 iuv;

uniform highp float iTime;
uniform highp vec2 iResolution;

uniform highp vec4 backgroundColor;

uniform highp vec4 topRightCircleColor1;
uniform highp vec4 topRightCircleColor2;
uniform highp vec4 bottomLeftCircleColor1;
uniform highp vec4 bottomLeftCircleColor2;

highp vec4 Background(highp vec2 uv)
{                
    return backgroundColor;
}

highp float hash21(highp vec2 co)
{
    return fract(sin(dot(co, vec2(12.9898, 78.233))) * 43758.5453);
}

highp vec2 hash22(highp vec2 co)
{
    highp float x = hash21(co);
    highp float y = hash21(vec2(x, co.y));
    return vec2(x, y);
}

highp vec4 Circle(highp vec2 uv, highp vec2 scale, highp float r, highp vec4 color1, highp vec4 color2)
{
    uv /= scale;
    highp float d = smoothstep(r * 0.3, r, length(uv));
    
    return mix(color1, color2, d);
}

highp float FrameCircleDist(highp vec2 uv, highp float r)
{
    return abs(length(uv) - r);
}

highp vec4 FrameCircle(highp vec2 uv, highp float r, highp float thickness, highp float angle)
{
    highp float m = FrameCircleDist(uv, r);
    m = smoothstep(thickness, thickness - 0.002, m);
    highp float theta = angle;
    highp float d = m * dot(vec2(sin(theta), cos(theta)), uv);    

    return vec4(m, m, m, d);
}

highp vec4 Blend(highp vec4 col1, highp vec4 col2)
{
    return vec4(mix(vec3(col1.xyz), vec3(col2.xyz), col2.w * 0.6), 1.);
}

highp float CirclePoolMask(highp vec2 uv, highp float size, highp float speed)
{
    uv *= size;
    highp vec2 id = floor(uv);
    uv = fract(uv) - 0.5;

    highp vec2 r = hash22(id);

    highp float d = 0.;

    if (fract(r.x + r.y) < 0.4)
    {
        d = length(uv - sin(r * iTime * 0.1 * speed) * 0.35);
        d = smoothstep(0.08, 0.05, d);                 
    }

    return d;
}

void main()
{
    highp vec2 uv = iuv.xy - 0.5;
    uv.x *= iResolution.x / iResolution.y;

    highp vec4 col = vec4(0.);
    col = Background(uv);
    highp vec4 circle1 = Circle(uv - vec2(0.7, 0.5), vec2(1.45, 1.), 0.85, topRightCircleColor1, topRightCircleColor2);
    circle1 *= (sin(iTime) * 0.5 + 0.5) * 0.2 + 0.8;
    col = Blend(col, circle1);
    highp vec4 circle2 = Circle(uv - vec2(-1.1, -0.8), vec2(1.), 1.08, bottomLeftCircleColor1, bottomLeftCircleColor2);
    circle2 *= (sin(iTime + 3.) * 0.5 + 0.5) * 0.2 + 0.8;
    col = Blend(col, circle2);

    highp vec4 frameCircle1 = FrameCircle(uv / vec2(1.15, 1.) - vec2(0.42, 0.53), 0.28, 0.004, 3.4);
    col = Blend(col, frameCircle1);

    highp vec4 frameCircle2 = FrameCircle(uv / vec2(1.1, 1.1) - vec2(-0.83, -0.3), 0.23, 0.004, 0.8);
    col = Blend(col, frameCircle2);

    highp float mask = CirclePoolMask(uv, 5., 3.);
    highp vec4 poolColor = vec4(vec3(mask), 0.4);
    col = Blend(col, poolColor);
    
    gl_FragColor = col;
}