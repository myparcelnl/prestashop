import {FetchClient, GetCarriers, createPublicSdk} from '@myparcel/sdk';
import fs from 'fs';
import path from 'path';
import fetch from 'node-fetch';
import sharp from 'sharp';

// eslint-disable-next-line no-underscore-dangle
const __dirname = path.dirname(new URL(import.meta.url).pathname);

/**
 * @type {import('@myparcel-pdk/app-builder').PdkBuilderCommand} downloadCarrierLogos
 */

export const downloadCarrierLogos = async ({debug}) => {
  const sdk = createPublicSdk(new FetchClient(), [new GetCarriers()]);

  const carriers = await sdk.getCarriers();

  await Promise.all(
    carriers.map(async (carrier) => {
      const imageUrl = `https://assets.myparcel.nl${carrier.meta.logo_svg}`;

      const response = await fetch(imageUrl);

      if (!response.ok) {
        // eslint-disable-next-line no-console
        debug(`Could not download image for ${carrier.name}`);
        return;
      }

      const imageBuffer = await response.arrayBuffer();

      fs.mkdirSync(path.resolve(__dirname, 'carrier-logos'), {recursive: true});

      const imagePath = path.resolve(__dirname, `carrier-logos/${carrier.name}`);

      const pngBackground = {r: 0, g: 0, b: 0, alpha: 0};
      const jpgBackground = {r: 255, g: 255, b: 255, alpha: 1};

      const resizeOptions = {
        fit: 'contain',
        width: 40,
        height: 40,
      };

      const image = sharp(imageBuffer);

      await image
        .clone()
        .flatten({background: jpgBackground})
        .resize({...resizeOptions, background: jpgBackground})
        .jpeg()
        .toFile(path.resolve(__dirname, `${imagePath}.jpg`));

      await image
        .clone()
        .resize({...resizeOptions, background: pngBackground})
        .png()
        .toFile(path.resolve(__dirname, `${imagePath}.png`));
    }),
  );
};
