
import { Controller, Get, Post, Body, Param, Put, Delete, HttpCode, HttpStatus, ParseIntPipe } from '@nestjs/common';
import { AnimalesService } from './animales.service';
import { CreateAnimalDto } from './dto/create-animal.dto';
import { UpdateAnimalDto } from './dto/update-animal.dto';

@Controller(['v1/animales', 'v1/potreros', 'v1/razas'])
export class AnimalesController {
  constructor(private readonly animalesService: AnimalesService) {}

  @Post()
  @HttpCode(HttpStatus.CREATED)
  create(@Body() createAnimalDto: CreateAnimalDto) {
    return this.animalesService.create(createAnimalDto);
  }

  @Get()
  findAll() {
    return this.animalesService.findAll();
  }

  @Get(':id')
  findOne(@Param('id', ParseIntPipe) id: number) {
    return this.animalesService.findOne(id);
  }

  @Put(':id')
  update(@Param('id', ParseIntPipe) id: number, @Body() updateAnimalDto: UpdateAnimalDto) {
    return this.animalesService.update(id, updateAnimalDto);
  }

  @Delete(':id')
  @HttpCode(HttpStatus.NO_CONTENT)
  remove(@Param('id', ParseIntPipe) id: number) {
    return this.animalesService.remove(id);
  }

  // Rutas anidadas mapeadas a 'v1/potreros' y 'v1/razas'
  @Get(':potreroId/animales')
  findByPotrero(@Param('potreroId', ParseIntPipe) potreroId: number) {
    return this.animalesService.findByPotrero(potreroId);
  }

  @Get(':razaId/animales')
  findByRaza(@Param('razaId', ParseIntPipe) razaId: number) {
    return this.animalesService.findByRaza(razaId);
  }
}